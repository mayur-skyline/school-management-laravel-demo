<?php

namespace App\Http\Controllers\AstNext;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dbglobal\Model_str_lead_sp_info;
use App\Libraries\Encdec;
use App\Services\AstNextEditStaffDataServiceProvider;

class AstNextEditStaffDataController extends Controller
{
    
    public function __construct() {
        $this->str_lead_sp_info_model = new Model_str_lead_sp_info();
        $this->edit_staff_tile = Config('constants.language_page_id.edit_staff_tile');
        $this->common_data = Config('constants.language_page_id.common_data');
        $this->encdec = new Encdec();
        $this->editStaffServiceProvider = new AstNextEditStaffDataServiceProvider();
    }

    public function fetchStaffdata(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $request_data = $request->all();
        
        $get_staff_data = $this->editStaffServiceProvider->getstaffData($request_data);
        return $get_staff_data;
    }
    
    public function addStaffdata(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $request_data = $request->all();
        
        $add_staff_data = $this->editStaffServiceProvider->storeStaffData($school_id, $request_data);
        return $add_staff_data;
    }
    
    public function getEditStaffView(Request $request, $pupil_id) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);

        $data = $this->editStaffServiceProvider->editstaffView($school_id, $pupil_id);
        return $data;
    }
    
    public function editStaff(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        
        $edit_staff_data = $this->editStaffServiceProvider->editstaffdata($school_id, $request);
        return $edit_staff_data;
    }
    
    public function deleteStaff(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $delete_id = explode(',', $request->get('pupil_id'));
       
        $delete_staff = $this->editStaffServiceProvider->deletestaffdata($school_id, $delete_id);
        return $delete_staff;
    }
    
    public function exportStaffData(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $request_data = $request->all();
        
        $export_data = $this->editStaffServiceProvider->exportStaffLogins($school_id, $request_data);
        return $export_data;
    }
    
    public function leadSpView(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        
        $lead_sp = $this->editStaffServiceProvider->leadSp($school_id);
        return $lead_sp;
        
    }
    
    public function updatedLeadSpStatus(Request $request) {
        $school_id = $request->get('school_id');
        
        $user_id = $this->encdec->encrypt_decrypt('decrypt', request()->get('user_id'));
        $updatedlead = array('sp_id' => $user_id);
        $update_lead = $this->str_lead_sp_info_model->updateLeadStatus($school_id, $updatedlead);
        return ["message" => "Lead SP has been updated successfully."];
    }
    
    public function updateCampusLeadSp(Request $request) {
        $school_id = $request->get('school_id');
        
        $update_campus_sp = $this->editStaffServiceProvider->updateLeadSp($school_id, $request);
        return $update_campus_sp;
    }

}
