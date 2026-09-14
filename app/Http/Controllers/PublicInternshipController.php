<?php
namespace App\Http\Controllers;
use App\Models\InternshipProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class PublicInternshipController extends Controller {
 public function register(string $token){$program=InternshipProgram::where('public_token',$token)->firstOrFail();return view('internships.public.register',compact('program'));}
 public function store(Request $request,string $token){$program=InternshipProgram::where('public_token',$token)->firstOrFail();abort_unless($program->isRegistrationOpen(),422,'Pendaftaran magang belum dibuka atau sudah ditutup.');$data=$request->validate(['name'=>'required|string|max:255','student_number'=>['required','string','max:100',Rule::unique('internship_participants')->where('internship_program_id',$program->id),Rule::unique('users','username')],'major'=>'required|string|max:255','institution'=>'required|string|max:255','placement_unit'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','email'=>'required|email|max:255','password'=>'required|string|min:8|confirmed'],['student_number.unique'=>'NIS/NIM sudah digunakan sebagai akun atau sudah terdaftar pada program ini.']);DB::transaction(function()use($data,$program){$user=User::create(['name'=>$data['name'],'username'=>trim($data['student_number']),'password'=>Hash::make($data['password']),'role'=>'intern','bidang'=>$data['placement_unit'],'user_type'=>'internship','user_type_status'=>'pending']);$program->participants()->create(['user_id'=>$user->id,'name'=>$data['name'],'student_number'=>$data['student_number'],'major'=>$data['major'],'institution'=>$data['institution'],'placement_unit'=>$data['placement_unit'],'start_date'=>$data['start_date'],'end_date'=>$data['end_date'],'email'=>$data['email']]);});$studentNumber=$data['student_number'];return view('internships.public.registered',compact('program','studentNumber'));}
}