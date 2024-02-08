<?php

namespace App\Http\Controllers;

use App\Models\ReservedSchedules;
use App\Models\TimeScheduleModel;
use App\Models\LogsModel;
use App\Models\ListofReservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\MailNotify;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

class ReservedSchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */

     //Saving of the Reservation
    public function createReservation(Request $request)
    {
        $date = new \DateTime('today');

        $time_start = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_start);
        $time_end = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_end);

        $noConflict = true;

        foreach (ReservedSchedules::where('conference_room', $request->conference_room)
                                    ->where('date', $request->date)
                                    ->where('status', '<>', 'Canceled')
                                    ->cursor() as $reserved) {

            $dbTimeStart = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_start']);
            $dbTimeEnd = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_end']);

            if(($dbTimeStart >= $time_start AND $dbTimeEnd <= $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd >= $time_end) OR
               ($dbTimeStart >= $time_start AND $dbTimeEnd >= $time_end AND $dbTimeStart < $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd > $time_start AND $dbTimeEnd <= $time_end)){
                $noConflict = false;
                break;
            }
        }

        if($noConflict) {

            //Saving as Event in the Google Calendar
            // Event::create([
            //     'name' => $request->department.' [ '.$request->purpose.' ]',
            //     'startDateTime' => Carbon::parse($request->date.' '.$request->time_start, 'Asia/Manila'),
            //     'endDateTime' => Carbon::parse($request->date.' '.$request->time_end, 'Asia/Manila'),
            // ]);
            $event = new Event;

            $event->name = $request->department.' [ '.$request->purpose.' ]';
            $event->startDateTime = Carbon::parse($request->date.' '.$request->time_start, 'Asia/Manila');
            $event->endDateTime = Carbon::parse($request->date.' '.$request->time_end, 'Asia/Manila');
            $event->description = $request->conference_room.'<br />Reserved by: '.$request->employee_name;
            $event->setColorId($this->getEventColorID($request->conference_room));

            $newEvent = $event->save();
            //End Saving as Event

            $newreservation = new ReservedSchedules;
            $newreservation->conference_room = $request->conference_room;
            $newreservation->date = $request->date;
            $newreservation->time_start = $request->time_start;
            $newreservation->time_end = $request->time_end;
            $newreservation->employee_name = $request->employee_name;
            $newreservation->email = $request->email;
            $newreservation->department = $request->department;
            $newreservation->purpose = $request->purpose;
            $newreservation->equipment = $request->equipment ;
            $newreservation->ct_arrangement = $request->ct_arrangement ;
            $newreservation->status = "Reserved" ;
            $newreservation->event_id = $newEvent->id; //add column on the db table

            $newreservation->save();

            $timeStart = $this->changeto12HourFormat($request->time_start);
            $timeEnd = $this->changeto12HourFormat($request->time_end);

            $date = $request->date;
            $timestamp = strtotime($date);
            $formattedDate = date("F j, Y", $timestamp);

            $mail = array(
                'name' => $request->name,
                'conferenceRoom' => $request->conference_room,
                'date' => $formattedDate,
                'name' => $request->employee_name,
                'timeStart' => $timeStart,
                'timeEnd' => $timeEnd,
                'email' => $request->email,
                'department' => $request->department,
                'equipment' => $request->equipment,
                'ct_arrangement' => $request->ct_arrangement,
                'timeSlot' => $timeStart . ' to ' . $timeEnd,
                'status' => 'Reserved'
            );

            Mail::to($request->email)->send(new MailNotify($mail));

            $id = DB::table('reserved_schedules')
                        ->select('id')
                        ->orderBy('id', 'desc')
                        ->first()->id;

            // echo $id;
            $new_data = array(
                'id' => $id,
                'conferenceRoom' => $request->conference_room,
                'timeStart' => $timeStart,
                'timeEnd' => $timeEnd,
                'department' => $request->department
            );
            $date = now()->timezone('Asia/Manila'); // GETTING THE TIME ZONE IN PH

            $logs = new LogsModel();
            $logs->dateTime = $date;
            $logs->name = $request->employee_name;
            $logs->action_type = 'Create Reservation';
            $logs->table_affected = 'reserved_schedules';
            $logs->new_data = json_encode($new_data);

            $logs->save();

            return response()->json($id);
        } else
            return response()->json("Already Occupied.");
    }

    public function changeto12HourFormat($time){

        $time = explode(':', $time);
        $time[0] = intval($time[0]);
        if($time[0] < 12){
            $time = $time[0] . ':' . $time[1] . ' AM';
        }
        elseif($time[0] > 12){
            $time = $time[0] - 12 . ':' . $time[1] . ' PM';
        }else{
            $time = 12 . ':' . $time[1] . ' PM';
        }

        return $time;
    }

    public function fetchMeetings(Request $request){

        $data = DB::table('reserved_schedules as a')
                    ->select('a.id', 'a.employee_name as employeeName', 'a.purpose',
                    'a.date as dateSchedule', 'a.email as employeeEmail',
                    'a.department', 'a.equipment', 'a.ct_arrangement as ctArrangement',
                    'a.status', 'a.conference_room as conferenceRoom',
                    'a.time_start as startTime', 'a.time_end as endTime',
                    DB::raw('CONCAT(a.date, " ", a.time_start) as timeStart'),
                    DB::raw('CONCAT(a.date, " ", a.time_end) as timeEnd'))
                    ->where('a.conference_room', $request->conference_room)
                    ->where('a.status', '!=', 'Canceled')
                    ->get();

        foreach ($data as $value){

            $tempTimeStart = explode(':',$value->startTime);
            $tempTimeEnd = explode(':',$value->endTime);

            if($tempTimeStart[0] >= 13){
                $timeStart = intval($tempTimeStart[0]) - 12 . ":" . $tempTimeStart[1] . " PM";
            }
            elseif($tempTimeStart[0] == 12){
                $timeEnd = 12 . ":" . $tempTimeEnd[1] . " PM";
            }
            else{
                $timeStart = $value->startTime . " AM";
            }

            if($tempTimeEnd[0] >= 13){
                $timeEnd = intval($tempTimeEnd[0]) - 12 . ":" . $tempTimeEnd[1] . " PM";
            }
            elseif($tempTimeEnd[0] == 12){
                $timeEnd = 12 . ":" . $tempTimeEnd[1] . " PM";
            }
            else{
                $timeEnd = $value->endTime . " AM";
            }

            $value->timeSlot = $timeStart . " to " . $timeEnd;
        }

        return response()->json($data);
    }

    public function fetchAllReservations(Request $request){

        if($request->position == "Admin")
                $data = DB::table('reserved_schedules as a')
                            ->select('a.id', DB::raw('CONCAT(MONTHNAME(a.date), " ", DATE_FORMAT(a.date, "%d, %Y")) as date'),
                            'a.conference_room as conferenceRoom',
                            'a.employee_name as name', 'a.email', 'a.department', 'a.status',
                            'a.time_start as timeStart', 'a.time_end as timeEnd', 'a.equipment',
                            'a.ct_arrangement as arrangement', 'a.purpose')
                            ->orderby('a.date', 'desc')
                            ->orderby('a.time_start', 'desc')
                            ->orderby('a.conference_room')
                            ->get();
        else
                $data = DB::table('reserved_schedules as a')
                            ->select('a.id', DB::raw('CONCAT(MONTHNAME(a.date), " ", DATE_FORMAT(a.date, "%d, %Y")) as date'),
                            'a.conference_room as conferenceRoom',
                            'a.employee_name as name', 'a.email', 'a.department', 'a.status',
                            'a.time_start as timeStart', 'a.time_end as timeEnd', 'a.equipment',
                            'a.ct_arrangement as arrangement', 'a.purpose')
                            ->orderby('a.date', 'desc')
                            ->orderby('a.time_start', 'desc')
                            ->orderby('a.conference_room')
                            ->where('a.email', $request->email)
                            ->get();

        foreach ($data as $value){
            $value->timeStart = $this->changeto12HourFormat($value->timeStart);
            $value->timeEnd = $this->changeto12HourFormat($value->timeEnd);
            $value->timeSlot = $value->timeStart . " to " . $value->timeEnd;
        }

        return response()->json($data);
    }

    public function updateReservation(Request $request){
        $data = $request->all();
        $date = new \DateTime('today');

        $time_start = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_start);
        $time_end = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_end);

        $noConflict = true;

        foreach (ReservedSchedules::where('conference_room', $request->conference_room)
                                    ->where('id', '<>', $request->id)
                                    ->where('date', $request->date)
                                    ->where('status', '<>', 'Canceled')
                                    ->cursor() as $reserved) {

            $dbTimeStart = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_start']);
            $dbTimeEnd = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_end']);

            if(($dbTimeStart >= $time_start AND $dbTimeEnd <= $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd >= $time_end) OR
               ($dbTimeStart >= $time_start AND $dbTimeEnd >= $time_end AND $dbTimeStart < $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd > $time_start AND $dbTimeEnd <= $time_end)){
                $noConflict = false;
                break;
            }
        }

        if($noConflict){
            $reserved_schedules = ReservedSchedules::find($request->id);

            foreach ($data as $key => $value) {
                $reserved_schedules->{$key} = $value;
            }

            $timeStart = $this->changeto12HourFormat($request->time_start);
            $timeEnd = $this->changeto12HourFormat($request->time_end);

            $date = $request->date;
            $timestamp = strtotime($date);
            $formattedDate = date("F j, Y", $timestamp);

            $mail = array(
                'name' => $request->employee_name,
                'email' => $request->email,
                'timeStart' => $timeStart,
                'timeEnd' => $timeEnd,
                'timeSlot' => $timeStart . ' to ' . $timeEnd,
                'date' => $formattedDate,
                'equipment' => $request->equipment,
                'ct_arrangement' => $request->ct_arrangement,
                'conferenceRoom' => $request->conference_room,
                'department' => $request->department,
                'status' => 'Rescheduled',
            );

            Mail::to($request->email)->send(new MailNotify($mail));
            // echo $id;
            $new_data = array(
                'id' => $request->id,
                'conferenceRoom' => $request->conference_room,
                'timeStart' => $timeStart,
                'timeEnd' => $timeEnd,
                'date' => $formattedDate,
                'department' => $request->department
            );
            $date = now()->timezone('Asia/Manila'); // GETTING THE TIME ZONE IN PH

            $logs = new LogsModel();
            $logs->dateTime = $date;
            $logs->name = $request->employee_name;
            $logs->action_type = 'Create Reservation';
            $logs->table_affected = 'reserved_schedules';
            $logs->old_data = json_encode($reserved_schedules);
            $logs->new_data = json_encode($new_data);

            $logs->save();

            $result = $reserved_schedules->save();

            //Update the event on the Google Calendar
            $event = Event::find($reserved_schedules->event_id);

            $event->name = $reserved_schedules->department.' [ '.$reserved_schedules->purpose.' ]';
            $event->startDateTime = Carbon::parse($reserved_schedules->date.' '.$reserved_schedules->time_start, 'Asia/Manila');
            $event->endDateTime = Carbon::parse($reserved_schedules->date.' '.$reserved_schedules->time_end, 'Asia/Manila');
            $event->save();

            return response()->json($result);
        }
        else return response()->json("Already Occupied.");
    }

    public function getTimeSlot(Request $request){

        $data = DB::table('reserved_schedules as a')
                    ->join('timeSchedtb as b', 'a.id', 'b.reserveID')
                    ->select(DB::raw('CONCAT(b.timeStart, " - " ,b.timeEnd) as timeSlot'))
                    ->where('a.conference_room', $request->conferenceRoom)
                    ->where('a.date', $request->dateSchedule)
                    ->where('a.status', '!=', 'Canceled')
                    ->orderBy('b.timeStart')
                    ->get();

        return response()->json($data);

    }

    public function checkTimeRange(Request $request){

        $date = new \DateTime('today');

        $time_start = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_start);
        $time_end = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $request->time_end);

        $noConflict = true;

        foreach (ReservedSchedules::where('conference_room', $request->conference_room)
                                    ->where('id', '<>', $request->id)
                                    ->where('date', $request->date)
                                    ->where('status', '<>', 'Canceled')
                                    ->cursor() as $reserved) {

            $dbTimeStart = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_start']);
            $dbTimeEnd = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $reserved['time_end']);

            if(($dbTimeStart >= $time_start AND $dbTimeEnd <= $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd >= $time_end) OR
               ($dbTimeStart >= $time_start AND $dbTimeEnd >= $time_end AND $dbTimeStart < $time_end) OR
               ($dbTimeStart <= $time_start AND $dbTimeEnd > $time_start AND $dbTimeEnd <= $time_end)){
                $noConflict = false;
                break;
            }
        }

        if($noConflict) return response()->json(true);
        else return response()->json(false);

    }

    public function cancelReservation(Request $request){

        DB::select('UPDATE reserved_schedules SET status = "Canceled" WHERE id = \''.$request->id.'\'');

        $dataEmail = DB::table('reserved_schedules')
                        ->select('employee_name', 'email', 'event_id')
                        ->where('id', $request->id)
                        ->get();


        $mail = array(
            'name' => $dataEmail[0]->employee_name,
            'status' => 'Canceled',
        );
        $new_data = array(
            'id' => $request->id,
            'status' => 'Canceled'
        );

        $date = now()->timezone('Asia/Manila'); // GETTING THE TIME ZONE IN PH

        $logs = new LogsModel();
        $logs->dateTime = $date;
        $logs->name = $dataEmail[0]->employee_name;
        $logs->action_type = 'Canceled Reservation';
        $logs->table_affected = 'reserved_schedules';
        $logs->new_data = json_encode($new_data);

        $logs->save();

        Mail::to($dataEmail[0]->email)->send(new MailNotify($mail));

        //Delete event on the Google Calendar
        $event = Event::find($dataEmail[0]->event_id);

        $event->delete();

        return response()->json("Update successfully");
    }

    public function getUsers(Request $request){

        $data = DB::table('users')
                ->select('*')
                ->get();

        return response()->json($data);
    }

    public function updateUser(Request $request){

        DB::select("UPDATE users
                    SET name = \"".$request->name."\",
                    department =\"".$request->department."\",
                    position =\"".$request->position."\",
                    status = \"".$request->status."\"
                    WHERE email = \"".$request->email."\" ");

        return response()->json("User update successfully.");
    }

    public function updateUserStatus(Request $request){

        DB::select('UPDATE users
                        SET status = \''.$request->status.'\'
                        WHERE email = \''.$request->email.'\' ');

        return response()->json("User update status successfully");
    }

    public function getLogs(){

        $data = DB::table('logs')
                    ->select(DB::raw('CONCAT(MONTHNAME(dateTime), " ", DATE_FORMAT(dateTime, "%d, %Y")) as date'),
                                DB::raw('DATE_FORMAT(dateTime, "%h:%i %p") as time'),
                                'name', 'table_affected', 'action_type', 'old_data', 'new_data')
                    ->orderBy('dateTime', 'desc')
                    ->get();

        return response()->json($data);
    }

    public function reservationReminder(){
        $today = date("Y-m-d");

        // Get schedules that have the same date today
        $data = DB::table('reserved_schedules')
            ->where('date', $today)
            ->where('status', 'Reserved')
            ->get();

        foreach ($data as $obj) {
            $timezone = new \DateTimeZone('Asia/Manila');
            $crrntTime = new \DateTime('now', $timezone); // Get the current time today

            $current_time = $crrntTime->format('H:i');

            $startTime = new \DateTime($obj->time_start); // Get the start time of the schedule
            $endTime = new \DateTime($obj->time_end); // get the start time of the schedule
            $currentTime = new \DateTime($current_time); // The current time today

            // Calculate the difference in minutes between the current time and the start time
            // $interval = $currentTime->diff($startTime)->h * 60 + $currentTime->diff($startTime)->i;
            if ($currentTime > $startTime) {
                $interval = $currentTime->diff($startTime);
                $interval = -$interval->h * 60 - $interval->i;
            } else {
                $interval = $currentTime->diff($startTime);
                $interval = $interval->h * 60 + $interval->i;
            }

            // When the interval is 180 minutes (3 hours) or less, send a reminder
            if ($interval <= 180 && $interval > 150) {
                $timeStart = $this->changeto12HourFormat($obj->time_start);
                $timeEnd = $this->changeto12HourFormat($obj->time_end);

                $date = $obj->date;
                $timestamp = strtotime($date);
                $formattedDate = date("F j, Y", $timestamp);

                $mail = array(
                    'name' => $obj->employee_name,
                    'email' => $obj->email,
                    'timeStart' => $timeStart,
                    'timeEnd' => $timeEnd,
                    'timeSlot' => $timeStart . ' to ' . $timeEnd,
                    'date' => $formattedDate,
                    'equipment' => $obj->equipment,
                    'ct_arrangement' => $obj->ct_arrangement,
                    'conferenceRoom' => $obj->conference_room,
                    'department' => $obj->department,
                    'status' => 'Reminder',
                    'hour' => '3 hours',
                );

                Mail::to($obj->email)->send(new MailNotify($mail));
            }

            else if($interval <= 60 && $interval > 30){
                // unset($data[$key]);
                $timeStart = $this->changeto12HourFormat($obj->time_start);
                $timeEnd = $this->changeto12HourFormat($obj->time_end);

                $date = $obj->date;
                $timestamp = strtotime($date);
                $formattedDate = date("F j, Y", $timestamp);

                $mail = array(
                    'name' => $obj->employee_name,
                    'email' => $obj->email,
                    'timeStart' => $timeStart,
                    'timeEnd' => $timeEnd,
                    'timeSlot' => $timeStart . ' to ' . $timeEnd,
                    'date' => $formattedDate,
                    'equipment' => $obj->equipment,
                    'ct_arrangement' => $obj->ct_arrangement,
                    'conferenceRoom' => $obj->conference_room,
                    'department' => $obj->department,
                    'status' => 'Reminder',
                    'hour' => '1 hour',
                );

                Mail::to($obj->email)->cc("mis@barbizonfashion.com")->send(new MailNotify($mail));
            }


            if ($currentTime >= $endTime) {
                // The Reservation is completed.
                $mail2 = array(
                    'name' => $obj->employee_name,
                    'status' => 'Completed',
                );

                Mail::to($obj->email)->cc("mis@barbizonfashion.com")->send(new MailNotify($mail2));

                DB::table('reserved_schedules')
                    ->where('id', $obj->id)
                    ->update(['status' => 'Completed']);
            }
        }
    }

    public function reservationReminder1Hour(){
        $today = date("Y-m-d");

        //get schedules that has same date today
        $data = DB::table('reserved_schedules')
            ->where('date', $today)
            ->where('status', 'Reserved')
            ->get();

        foreach ($data as $key => $obj) {
            $timezone = new \DateTimeZone('Asia/Manila');
            $crrntTime = new \DateTime('now', $timezone); // get the current time today

            $current_time = $crrntTime->format('H:i');

            $startTime = new \DateTime($obj->time_start); // get the start time of the schedule
            $endTime = new \DateTime($obj->time_end); // get the start time of the schedule
            $currentTime = new \DateTime($current_time); // the current time today

            //get the difference of current time and the start time of the schedule in minutes
            if ($currentTime > $startTime) {
                $interval = $currentTime->diff($startTime);
                $interval = -$interval->h * 60 - $interval->i;
            } else {
                $interval = $currentTime->diff($startTime);
                $interval = $interval->h * 60 + $interval->i;
            }

            // when the interval is 30 minutes or less, then Remind
            if($interval <= 60 && $interval > 0){
                // unset($data[$key]);
                $timeStart = $this->changeto12HourFormat($obj->time_start);
                $timeEnd = $this->changeto12HourFormat($obj->time_end);

                $date = $obj->date;
                $timestamp = strtotime($date);
                $formattedDate = date("F j, Y", $timestamp);

                $mail = array(
                    'name' => $obj->employee_name,
                    'email' => $obj->email,
                    'timeStart' => $timeStart,
                    'timeEnd' => $timeEnd,
                    'timeSlot' => $timeStart . ' to ' . $timeEnd,
                    'date' => $formattedDate,
                    'equipment' => $obj->equipment,
                    'ct_arrangement' => $obj->ct_arrangement,
                    'conferenceRoom' => $obj->conference_room,
                    'department' => $obj->department,
                    'status' => 'Reminder',
                    'hour' => '1 hour',
                );

                Mail::to($obj->email)->cc("mis@barbizonfashion.com")->send(new MailNotify($mail));
            }

            if($currentTime >= $endTime){
                //The Reservation is completed.
                // print_r("completed id: ".$obj->id);
                $mail2 = array(
                    'name' => $obj->employee_name,
                    'status' => 'Completed',
                );

                Mail::to($obj->email)->cc("mis@barbizonfashion.com")->send(new MailNotify($mail2));

                DB::select('UPDATE reserved_schedules SET status = "Completed" WHERE id = \''.$obj->id.'\'');
            }

        }
    }
    //Get Color Event ID by Conference Room
    public function getEventColorID($conferenceRoom) {
        $bgColor = null;

        switch ($conferenceRoom) {
            case "AT HOME Conference Room":
                $bgColor = 1;
                break;
            case "BARBIZON Conference Room":
                $bgColor = 4;
                break;
            case "MONALISA Conference Room":
                $bgColor = 6;
                break;
            case "SASSA Conference Room":
                $bgColor = 8;
                break;
            case "SWIMLAB Conference Room":
                $bgColor = 10;
                break;
        }

        return $bgColor;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ReservedSchedules $reservedSchedules)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReservedSchedules $reservedSchedules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReservedSchedules $reservedSchedules)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReservedSchedules $reservedSchedules)
    {
        //
    }
}
