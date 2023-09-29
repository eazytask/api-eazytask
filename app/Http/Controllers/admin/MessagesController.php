<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\JobType;
use App\Models\Project;
use App\Models\TimeKeeper;
use App\Models\Message;
use App\Models\MessageReply;
use App\Models\MessageConfirm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MessagesController extends Controller
{
    public function index()
    {
        try {
            $projects = Project::where('company_code', Auth::user()->company_roles->first()->company->id)->orderBy('pName', 'asc')->get()->pluck('id')->toArray();
            
            $messages = Message::with('replies', 'confirms')->orderBy('created_at', 'DESC')->get();
            foreach ($messages as $key => $message) {
                if (empty(array_diff($message->list_venue, ["all"]))) {
                    // Thats all
                }elseif(!empty(array_diff($message->list_venue, $projects))) {
                    unset($messages[$key]);
                }
                // Access message properties
                $message->purposes = $message->getListVenue();
                $message->replies = $message->replies;
                $message->confirms = $message->confirms;
                if($message->need_confirm == 'Y') {
                    $message->my_confirm = MessageConfirm::where('message_id', $message->id)->where('user_id', Auth::user()->id)->count() > 0;
                }else{
                    $message->my_confirm = false;
                }
            }

            $messages = array_values($messages->toArray()); // 'reindex' array

            return send_response(true, 'Succesfully Fetch Messages', $messages);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function store(Request $request)
    {
        try {
            if (empty(array_diff($request->list_venue, ["all"]))) {
                $request->list_venue = Project::where('company_code', Auth::user()->company_roles->first()->company->id)->orderBy('pName', 'asc')->get()->pluck('id')->toArray();
            }
            
            $message = Message::create([
                'user_id' => Auth::user()->id,
                'heading' => $request->heading,
                'text' => $request->text,
                'need_confirm' => $request->need_confirm,
                'published' => 'Y',
                'publish_date' => date('Y-m-d'),
                'list_venue' => $request->list_venue,
            ]);

            return send_response(true, 'message successfully added', $message);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function storeReply(Request $request)
    {
        try {
            $reply = MessageReply::create([
                'user_id' => Auth::user()->id,
                'message_id' => $request->message_id,
                'text' => $request->text,
                'published' => 'Y',
                'publish_date' => date('Y-m-d'),
            ]);

            return send_response(true, 'reply successfully added', $reply);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function confirm(Request $request)
    {
        try {
            $confirm = MessageConfirm::create([
                'user_id' => Auth::user()->id,
                'message_id' => $request->message_id,
            ]);

            return send_response(true, 'confirm successfully added', $confirm);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function unconfirm(Request $request)
    {
        try {
            $confirm = MessageConfirm::where('user_id', Auth::user()->id)->where('message_id', $request->message_id)->delete();

            return send_response(true, 'confirm successfully removed', $confirm);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }
    }

    public function update(Request $request)
    {
        try {
            $message = Message::where('id', $request->message_id)->first();

            if (!empty($request->list_venue)) {
                if (empty(array_diff($request->list_venue, ["all"]))) {
                    $request->list_venue = Project::where('company_code', Auth::user()->company_roles->first()->company->id)->orderBy('pName', 'asc')->get()->pluck('id')->toArray();
                }
                
                $message->list_venue = $request->list_venue;
            }

            $message->heading = $request->heading;
            $message->text = $request->text;
            $message->need_confirm = $request->need_confirm;

            $message->save();  

            return send_response(true, 'message successfully updated', $message);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }      
    }

    public function destroy(Request $request) 
    {
        try {
            $message = Message::where('id', $request->message_id)->delete();

            return send_response(true, 'message successfully deleted', $message);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }     
    }

    public function updateReply(Request $request)
    {
        try {
            $message = MessageReply::where('id', $request->reply_id)->first();
            $message->text = $request->text;

            $message->save();  

            return send_response(true, 'reply successfully updated', $message);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }           
    }

    public function destroyReply(Request $request) 
    {
        try {
            $message = MessageReply::where('id', $request->reply_id)->delete();

            return send_response(true, 'reply successfully deleted', $message);
        } catch (\Throwable $e) {
            return send_response(false, 'something went wrong!', 400);
        }           
    }
}