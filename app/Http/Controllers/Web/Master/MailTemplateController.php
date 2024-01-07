<?php

namespace App\Http\Controllers\Web\Master;


use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Http\Controllers\Web\BaseController;
use App\Models\Access\Permission;
use App\Models\Master\MailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class MailTemplateController extends BaseController
{
     /**
     * The mailTemplate model instance.
     *
     * @var \App\Models\Access\mailTemplate
     */
    protected $mailTemplate;

    /**
     * MailTemplateController constructor.
     *
     * @param \App\Models\MailTemplae $MailTemplae
     * @param ImageUploaderContract $imageUploader
     */
    public function __construct(MailTemplate $mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;
    }

    public function index()
    {
        $page = trans('pages_names.view_mail_template');

        $main_menu = 'settings';
        $sub_menu = 'mail_template';

        return view('admin.master.mailTemplate.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function fetch(QueryFilterContract $queryFilter)
    {
        $query = $this->mailTemplate->query();

        $results = $queryFilter->builder($query)->customFilter(new CommonMasterFilter)->paginate();

        return view('admin.master.mailTemplate._mailTemplate', compact('results'));
    }

    public function create()
    {
        $page = trans('pages_names.add_mail_template');

        $main_menu = 'settings';
        $sub_menu = 'mail_template';

        return view('admin.master.mailTemplate.create', compact('page', 'main_menu', 'sub_menu'));
    }

    public function store(Request $request)
    {
        if (env('APP_FOR')=='demo') {
            $message = trans('succes_messages.you_are_not_authorised');

            return redirect('mail_templates')->with('warning', $message);
        }

        Validator::make($request->all(), [
            'mail_type' => 'required',
            'description' => 'required'
        ])->validate();

        $check_if_exists = $this->mailTemplate->where('mail_type', $request->mail_type)->whereActive(true)->exists();
        
        if ($check_if_exists) 
        {
            throw ValidationException::withMessages(['mail_type' => __('Mail Type already exists')]);
        }

        $created_params = $request->only(['mail_type','description']);
        $created_params['active'] = 1;

        $this->mailTemplate->create($created_params);

        $message = trans('succes_messages.mail_template_added_succesfully');

        return redirect('mail_templates')->with('success', $message);
    }

    public function getById(MailTemplate $mailTemplate)
    {

        $page = trans('pages_names.edit_mail_template');

        $main_menu = 'settings';
        $sub_menu = 'mail_template';
        $item = $mailTemplate;
        return view('admin.master.mailTemplate.update', compact('item', 'page', 'main_menu', 'sub_menu'));
    }

    public function update(Request $request, MailTemplate $mailTemplate)
    {
        if (env('APP_FOR')=='demo') {
            $message = trans('succes_messages.you_are_not_authorised');

            return redirect('mail_templates')->with('warning', $message);
        }


        Validator::make($request->all(), [
            'mail_type' => 'required',
            'description' => 'required'
        ])->validate();

        $updated_params = $request->all();
        $mailTemplate->update($updated_params);
        $message = trans('succes_messages.mail_template_updated_succesfully');
        return redirect('mail_templates')->with('success', $message);
    }

    public function toggleStatus(MailTemplate $mailTemplate)
    {
       if (env('APP_FOR')=='demo') {
            $message = trans('succes_messages.you_are_not_authorised');

            return redirect('mail_templates')->with('warning', $message);
        }
        $status = $mailTemplate->isActive() ? false: true;
        $mailTemplate->update(['active' => $status]);

        $message = trans('succes_messages.mail_template_status_changed_succesfully');
        return redirect('mail_templates')->with('success', $message);
    }

    public function delete(MailTemplate $mailTemplate)
    {
        if (env('APP_FOR')=='demo') {
            $message = trans('succes_messages.you_are_not_authorised');

            return redirect('mail_template')->with('warning', $message);
        }
        $mailTemplate->delete();

        $message = trans('succes_messages.mail_template_deleted_succesfully');
        return redirect('mail_templates')->with('success', $message);
    }
}
