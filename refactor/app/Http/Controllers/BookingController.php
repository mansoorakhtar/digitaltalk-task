<?php

namespace DTApi\Http\Controllers;


use DTApi\Http\Requests;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use DTApi\Models\{Distance, Job};


/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }
        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return response($this->repository->with('translatorJobRel.user')->find($id));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);
        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }
        return null;
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request) {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJob($data, $user);
        return response($response);
    }

    /**
     * accept job with id
     * @param Request $request
     * @return mixed 
     */
    public function acceptJobWithId(Request $request){
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);
        return response($response);
    }

    /**
     * cancel job
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request) {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->cancelJobAjax($data, $user);
        return response($response);
    }

    /**
     * End job
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request){
        return response($this->repository->endJob($request->all()));
    }


    /**
     * Customer Not Call
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request){
        return response($this->repository->customerNotCall($request->all()));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request) {
        $user = $request->__authenticatedUser;
        return response($this->repository->getPotentialJobs($user));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request) {
        $data = $request->all();

        $distance = !empty($data['distance']) ? $data['distance'] : "";
        $time = !empty($data['time']) ? $data['time'] : "";
        $jobid = !empty($data['jobid']) ? $data['jobid'] : "";
        $session = !empty($data['session_time']) ? $data['session_time'] : "";
        $manually_handled = $data['manually_handled'] == 'true' ? 'yes' : 'no';  
        $by_admin = $data['by_admin'] == 'true' ? 'yes' : 'no';  
        $admincomment = !empty($data['admincomment']) ? $data['admincomment'] : "";    

        $flagged = 'no';
        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        }    
        
        if ($time || $distance) {
            $affectedRows = Distance::where('job_id', '=', $jobid)
                                ->update([
                                    'distance' => $distance, 
                                    'time' => $time
                                ]);
        }
            
        
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            $affectedRows1 = Job::where('id', '=', $jobid)
                                ->update([
                                    'admin_comments' => $admincomment, 
                                    'flagged' => $flagged, 
                                    'session_time' => $session, 
                                    'manually_handled' => $manually_handled, 
                                    'by_admin' => $by_admin
                                ]);
        }
        return response('Record updated!');
    }


    /**
     * @param Request $request
     * @return mixed
    */
    public function reopen(Request $request) {
        return response($this->repository->reopen($request->all()));
    }


    /**
     * @param Request $request
     * @return mixed
    */
    public function resendNotifications(Request $request) {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');
        return response(['success' => 'Push sent']);
    }


    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request) {
        $jobId = $request->input('jobid');
        $job = $this->repository->find($jobId);
        $job_data = $this->repository->jobToData($job);
        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
