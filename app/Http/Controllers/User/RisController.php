<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RisRequest;
use App\Models\RisItem;
use App\Models\Supply; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RisController extends Controller
{
    public function create()
    {
        $yearMonth = date('Y-m');
        $count = RisRequest::whereMonth('created_at', date('m'))->count() + 1;
        $risNumber = 'RIS-' . $yearMonth . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $supplies = Supply::orderBy('article', 'asc')->get();

        return view('user.ris.create', compact('risNumber', 'supplies'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $ris = new RisRequest();
        $ris->user_id = $user->id; 
        $ris->ris_no = $request->ris_no;
        $ris->entity_name = $request->entity_name;
        $ris->division = $request->unit_section;
        $ris->office = $request->office;
        $ris->fund_cluster = $request->fund_cluster;
        $ris->rcc = $request->center_code;
        $ris->purpose = implode('; ', array_filter(array_unique($request->purpose)));
        $ris->sig_requested_by = $request->requested_by;
        $ris->desig_requested = $request->desig_requested;
        $ris->date_requested = now()->toDateString();
        $ris->sig_approved_by = $request->approved_by;
        $ris->desig_approved = $request->desig_approved;
        $ris->sig_issued_by = $request->issued_by;
        $ris->desig_issued = $request->desig_issued;
        $ris->sig_received_by = $request->received_by;
        $ris->desig_received = $request->desig_received;
        $ris->status = 'Pending Staff Review';
        $ris->save();

        $itemCount = count($request->stock_no);
        for ($i = 0; $i < $itemCount; $i++) {
            if (!empty($request->description[$i])) {
                RisItem::create([
                    'ris_id' => $ris->id,
                    'stock_no' => $request->stock_no[$i],
                    'unit' => $request->unit_measure[$i],
                    'description' => $request->description[$i],
                    'req_quantity' => $request->quantity[$i],
                    'stock_avail' => 'N/A', // Automatically defaults to N/A for User Side
                    'remarks' => $request->remarks[$i],
                ]);
            }
        }

        return redirect('/user/ris/history')->with('msg', 'RIS Request successfully submitted!');
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        
        $firstName = trim($user->firstname);

        $query = RisRequest::with('items') 
            ->where(function($q) use ($user, $firstName) {
                $q->where('user_id', $user->id)
                  ->orWhere('sig_requested_by', 'LIKE', "%{$firstName}%");
            });

        if ($request->filled('search')) {
            $query->where('ris_no', 'like', '%' . trim($request->search) . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status == 'approved') {
                $query->where('status', 'Approved');
            } elseif ($status == 'pending') {
                $query->whereIn('status', ['Pending Staff Review', 'Forwarded to Admin', 'Pending']); 
            } elseif ($status == 'declined') {
                $query->whereIn('status', ['Declined', 'Cancelled', 'Rejected']);
            }
        }

        $perPage = $request->input('per_page', 5);
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('user.ris.history', compact('requests', 'perPage'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $firstName = trim($user->firstname);

        $req = RisRequest::with('items')
            ->where('id', $id)
            ->where(function($q) use ($user, $firstName) {
                $q->where('user_id', $user->id)
                  ->orWhere('sig_requested_by', 'LIKE', "%{$firstName}%");
            })
            ->firstOrFail();

        return view('user.ris.show', compact('req'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $firstName = trim($user->firstname);

        $req = RisRequest::with('items')
            ->where('id', $id)
            ->where(function($q) use ($user, $firstName) {
                $q->where('user_id', $user->id)
                  ->orWhere('sig_requested_by', 'LIKE', "%{$firstName}%");
            })
            ->firstOrFail();

        if ($req->status != 'Pending Staff Review') {
            return redirect('/user/ris/history')->with('msg', 'This request can no longer be edited as it is already being processed.');
        }

        $supplies = Supply::orderBy('article', 'asc')->get();

        return view('user.ris.edit', compact('req', 'supplies'));
    }

    public function update(Request $request, $id)
    {
        $ris = RisRequest::findOrFail($id);

        if ($ris->status != 'Pending Staff Review') {
            return redirect('/user/ris/history')->with('msg', 'This request can no longer be edited.');
        }

        $ris->update([
            'office' => $request->office,
            'division' => $request->unit_section ?? $ris->division,
            'fund_cluster' => $request->fund_cluster,
            'rcc' => $request->center_code,
            'purpose' => is_array($request->purpose) ? implode('; ', array_filter(array_unique($request->purpose))) : $request->purpose,
        ]);

        RisItem::where('ris_id', $ris->id)->delete();

        $itemCount = count($request->stock_no ?? []);
        for ($i = 0; $i < $itemCount; $i++) {
            if (!empty($request->description[$i])) {
                RisItem::create([
                    'ris_id' => $ris->id,
                    'stock_no' => $request->stock_no[$i],
                    'unit' => $request->unit_measure[$i] ?? null,
                    'description' => $request->description[$i],
                    'req_quantity' => $request->quantity[$i] ?? null,
                    'stock_avail' => 'N/A', // Automatically defaults to N/A for User Side Edit
                    'remarks' => $request->remarks[$i] ?? null,
                ]);
            }
        }

        return redirect('/user/ris/history')->with('msg', 'RIS Request successfully updated!');
    }
}