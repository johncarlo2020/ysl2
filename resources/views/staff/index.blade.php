@extends('layouts.admin')
@section('content')
<style>

        .staff-table-card .dt-container { padding: 0 20px 20px; }
        #staff-table th, #staff-table td { padding: 16px; vertical-align: middle; }
        #staff-table th { font-size: 11px; letter-spacing: .5px; color: #67748e; background: #f8fafc; border-bottom: 1px solid #e9edf3; }
        .staff-table-card .card-header { padding: 24px; border-bottom: 1px solid #edf0f5; }
        .staff-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; }
        .staff-search { display: flex; align-items: center; gap: 10px; border: 1px solid #dfe5ee; border-radius: 10px; padding: 0 14px; color: #8392ab; width: 320px; max-width: 100%; background: #f8fafc; }
        .staff-search:focus-within { border-color: #5e72e4; box-shadow: 0 0 0 3px #5e72e415; }
        .staff-search input { border: 0; outline: 0; background: transparent; padding: 12px 0; width: 100%; font-size: 14px; color: #344767; }
        .staff-export { position: relative; flex-shrink: 0; }
        .staff-export summary { list-style: none; cursor: pointer; background: #5e72e4; color: #fff; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .staff-export summary::-webkit-details-marker { display: none; }
        .staff-export summary:focus-visible { outline: 3px solid #b6bfff; outline-offset: 2px; }
        .staff-export-menu { position: absolute; right: 0; top: calc(100% + 8px); z-index: 20; min-width: 180px; padding: 6px; background: #fff; border: 1px solid #e9edf3; border-radius: 12px; box-shadow: 0 12px 32px #34476720; }
        .staff-export-menu button { display: flex; align-items: center; gap: 12px; width: 100%; border: 0; background: transparent; padding: 10px 12px; text-align: left; color: #344767; border-radius: 7px; font-size: 13px; }
        .staff-export-menu button:hover, .staff-export-menu button:focus-visible { background: #f0f2ff; color: #5e72e4; }
        .staff-export-menu i { width: 16px; }
        .staff-table-card .dt-buttons { display: none; }
        .staff-table-card .dt-layout-row:last-child { border-top: 1px solid #edf0f5; padding-top: 16px; margin-top: 16px; }
        .staff-table-card .dt-info, .staff-table-card .dt-length { color: #8392ab; font-size: 12px; }
        .staff-table-card .dt-length select { border: 1px solid #dfe5ee; border-radius: 8px; padding: 6px 10px; margin-right: 8px; color: #344767; background: #fff; }
        .staff-table-card .dt-paging .dt-paging-button { border: 1px solid #e9edf3 !important; border-radius: 8px !important; background: #fff !important; color: #67748e !important; min-width: 34px; padding: 7px 10px !important; margin: 0 3px; font-size: 12px; box-shadow: none !important; }
        .staff-table-card .dt-paging .dt-paging-button.current { background: #5e72e4 !important; border-color: #5e72e4 !important; color: #fff !important; }
        .staff-table-card .dt-paging .dt-paging-button:hover { background: #eef0ff !important; color: #5e72e4 !important; }
        .staff-table-card .dt-container .dt-paging .dt-paging-button.current,
        .staff-table-card .dt-container .dt-paging .dt-paging-button.current:hover,
        .staff-table-card .dt-container .dt-paging .dt-paging-button.current:focus {
            background: #5e72e4 !important;
            border-color: #5e72e4 !important;
            color: #fff !important;
        }
        .staff-table-card .dt-paging .dt-paging-button.disabled { opacity: .4; }
        @media (max-width: 576px) { .staff-toolbar { padding: 16px; gap: 10px; } .staff-export summary { padding: 12px; } .staff-table-card .dt-container { padding: 0 12px 16px; } }

        .user-timestamp { display: flex; flex-direction: column; gap: 4px; min-width: 130px; font-size: 12px; color: #344767; }
        .user-timestamp span { font-size: 11px; color: #8392ab; }
        .user-duration { display: inline-block; padding: 7px 10px; border-radius: 8px; background: #e7f5ec; color: #23864b; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .user-in-progress { color: #8392ab; font-size: 12px; white-space: nowrap; }
        #staff-table th { max-width: 190px; }
        .station-status { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; font-weight: 700; }
        .station-status.complete { background: #e7f5ec; color: #23864b; }
        .station-status.pending { background: #f1f2f5; color: #8392ab; }
        #staff-table td.dt-empty { padding: 48px 16px; background: #fff; text-align: center; }
        .staff-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .staff-empty-icon { display: flex; align-items: center; justify-content: center; width: 64px; height: 64px; margin-bottom: 6px; border-radius: 18px; background: #eef0ff; color: #5e72e4; font-size: 26px; }
        .staff-empty strong { color: #344767; font-size: 16px; font-weight: 600; }
        .staff-empty p { margin: 0; max-width: 320px; color: #8392ab; font-size: 13px; line-height: 1.6; white-space: normal; }
.staff-header{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}.staff-button{border:0;border-radius:10px;padding:12px 16px;background:#5e72e4;color:white;font-size:13px;font-weight:600;min-height:44px;cursor:pointer}.staff-button:hover{background:#4c60d2}.staff-button.secondary{background:#f1f2f5;color:#67748e}.staff-button:disabled{opacity:.5;cursor:wait}.staff-button:focus-visible{outline:3px solid #b6bfff;outline-offset:2px}.staff-badge{display:inline-flex;gap:6px;align-items:center;padding:6px 10px;border-radius:8px;background:#eef0ff;color:#5e72e4;font-size:12px}.staff-badge.station{background:#e7f5ec;color:#23864b}.staff-station-name{display:block;font-size:12px;color:#67748e;max-width:280px;white-space:normal}.staff-dialog{width:calc(100% - 32px);max-width:520px;max-height:90vh;overflow:auto;padding:28px;border:1px solid #edf0f5;border-radius:18px;color:#344767;box-shadow:0 24px 64px #34476730}.staff-dialog::backdrop{background:#172b4d80}.staff-dialog h2{font-size:20px;margin:0 0 8px}.staff-dialog p{font-size:13px;color:#67748e}.staff-dialog label{font-size:13px;margin-bottom:6px}.staff-dialog .form-control{padding:12px;border-radius:10px;background:#f8fafc;border-color:#dfe5ee}.staff-dialog-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:24px}.staff-errors{color:#b42332;font-size:13px;margin:12px 0}.staff-errors:empty{display:none}#staff-table td{font-size:13px;color:#344767}.staff-table-card{border:1px solid #edf0f5;border-radius:16px;overflow:hidden}.staff-success{background:#e7f5ec;color:#23864b;padding:16px;border-radius:12px;margin-top:24px}
</style>
@if(session('status'))<div class="staff-success" role="status">{{ session('status') }}</div>@endif
<div class="mt-4 mb-4 card staff-table-card">
    <div class="card-header staff-header"><div><h5 class="mb-1">Staff users</h5><p class="text-sm text-secondary mb-0">Manage registration access and assigned stations for the Tauri app.</p></div><button type="button" class="staff-button" id="add-staff"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>Add staff user</button></div>
    <div class="staff-toolbar"><label class="staff-search" for="staff-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="staff-search" type="search" placeholder="Search email, function or station" aria-label="Search staff" value="{{ request('search') }}"></label><span class="text-sm text-secondary">{{ $staff->count() }} staff accounts</span></div>
    <div class="table-responsive"><table id="staff-table" class="display" style="width:100%"><thead><tr><th>User ID</th><th>Email</th><th>Function</th><th>Assigned station</th><th>Actions</th></tr></thead><tbody>
    @foreach($staff as $member)
    <tr><td class="font-weight-bold">{{ $member->id }}</td><td>{{ $member->email }}</td><td><span class="staff-badge {{ $member->staff_function === 'station' ? 'station' : '' }}"><i class="fa-solid {{ $member->staff_function === 'station' ? 'fa-location-dot' : 'fa-id-card' }}" aria-hidden="true"></i>{{ $member->staff_function === 'register' ? 'Registration' : 'Station' }}</span></td><td>@if($member->station_id)<strong>Station {{ $member->station_id }}</strong><span class="staff-station-name">{{ $stations->firstWhere('id', $member->station_id)?->name }}</span>@else<span class="text-secondary">—</span>@endif</td><td><button type="button" class="staff-button secondary edit-staff" data-id="{{ $member->id }}" aria-label="Edit {{ $member->email }}"><i class="fa-solid fa-pen me-2" aria-hidden="true"></i>Edit</button></td></tr>
    @endforeach
    </tbody></table></div>
</div>
<dialog class="staff-dialog" id="staff-dialog" aria-labelledby="staff-dialog-title">
    <h2 id="staff-dialog-title">Add staff user</h2><p>Choose registration access or assign a station.</p>
    <form id="staff-form" action="{{ route('staff.store') }}" method="POST">
        @csrf
        <div id="staff-errors" class="staff-errors" role="alert"></div>
        <div class="mb-3"><label for="staff-email">Email</label><input class="form-control" id="staff-email" name="email" type="email" required maxlength="255" autocomplete="off"></div>
        <div class="mb-3"><label for="staff-function">Function</label><select class="form-control" id="staff-function" name="staff_function" required><option value="register">Registration</option><option value="station">Station</option></select></div>
        <div class="mb-3" id="staff-station-field"><label for="staff-station">Assigned station</label><select class="form-control" id="staff-station" name="station_id"><option value="">Select a station</option>@foreach($stations as $station)<option value="{{ $station->id }}">{{ $station->id }} · {{ $station->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label for="staff-password" id="staff-password-label">Password</label><input class="form-control" id="staff-password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password"><p class="mt-2 mb-0" id="staff-password-help">At least 12 characters.</p></div>
        <div class="mb-3"><label for="staff-password-confirmation">Confirm password</label><input class="form-control" id="staff-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
        <p id="staff-signout" hidden>Saving changes signs this staff member out of the Tauri app.</p>
        <div class="staff-dialog-actions"><button type="button" class="staff-button secondary" id="staff-cancel">Cancel</button><button class="staff-button" id="staff-save">Create account</button></div>
    </form>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = new DataTable('#staff-table', { order:[[0,'desc']], pageLength:10, layout:{topStart:null,topEnd:null,bottomStart:['pageLength','info'],bottomEnd:'paging'}, columnDefs:[{targets:4,orderable:false,searchable:false}], language:{emptyTable:'<div class="staff-empty"><span class="staff-empty-icon"><i class="fa-solid fa-user-gear" aria-hidden="true"></i></span><strong>No staff users yet</strong><p>Add a staff account to get started.</p></div>',zeroRecords:'<div class="staff-empty"><strong>No records found</strong><p>Try another email, function or station.</p></div>'} });
    const search = document.getElementById('staff-search'); search.addEventListener('input', () => table.search(search.value).draw());
    const members = @json($staffData);
    const dialog=document.getElementById('staff-dialog'), form=document.getElementById('staff-form'), errors=document.getElementById('staff-errors'), save=document.getElementById('staff-save'), cancel=document.getElementById('staff-cancel'), func=document.getElementById('staff-function'), station=document.getElementById('staff-station');
    let editing=null, opener=null, busy=false;
    function updateStation(){const needed=func.value==='station';document.getElementById('staff-station-field').hidden=!needed;station.required=needed;station.disabled=!needed;}
    function open(member, button){editing=member;opener=button;form.reset();errors.textContent='';document.getElementById('staff-dialog-title').textContent=member?'Edit staff user':'Add staff user';save.textContent=member?'Save changes':'Create account';document.getElementById('staff-email').value=member?.email||'';func.value=member?.staff_function||'register';station.value=member?.station_id||'';document.getElementById('staff-password').required=!member;document.getElementById('staff-password-confirmation').required=!member;document.getElementById('staff-password-label').textContent=member?'New password (optional)':'Password';document.getElementById('staff-password-help').textContent=member?'At least 12 characters. Leave blank to keep the current password.':'At least 12 characters.';document.getElementById('staff-signout').hidden=!member;updateStation();dialog.showModal();document.getElementById('staff-email').focus();}
    func.addEventListener('change',updateStation);document.getElementById('add-staff').onclick=function(){open(null,this);};
    document.getElementById('staff-table').addEventListener('click',event=>{const button=event.target.closest('.edit-staff');if(button)open(members[button.dataset.id],button);});
    cancel.onclick=()=>dialog.close();dialog.addEventListener('close',()=>opener?.focus());dialog.addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    form.addEventListener('submit',async event=>{event.preventDefault();if(busy)return;busy=true;save.disabled=cancel.disabled=true;save.textContent='Saving…';errors.textContent='';const body=new FormData(form);if(editing)body.set('_method','PUT');try{const response=await fetch(editing?@json(url('/admin/staff'))+'/'+editing.id:form.action,{method:'POST',headers:{Accept:'application/json'},body});const data=await response.json();if(!response.ok){errors.textContent=data.errors?Object.values(data.errors).flat().join(' '):(data.message||'Unable to save. Please try again.');return;}window.location.assign(@json(route('staff.index')));}catch(error){errors.textContent='Unable to save. Check your connection and try again.';}finally{busy=false;save.disabled=cancel.disabled=false;save.textContent=editing?'Save changes':'Create account';}});
});
</script>
@endsection
