<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Validators\LeadValidator;
use App\Http\Resources\LeadResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\{Rule, ValidationException};
use Illuminate\Support\Facades\{DB, Notification, Storage};

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() : JsonResource
    {
        $leads = Lead::query()
            ->when(request('creator') && auth()->user() && request('creator') == auth()->id(),
                fn($builder) => $builder
            )
            ->when(request('assign') && auth()->user() && request('assign') == auth()->id(),
                fn($builder) => $builder
            )
            ->when(request('status') && auth()->user() && request('status') == auth()->id(),
                fn($builder) => $builder->whereStatus(request('status'))
            )
            ->when(
                request('status') && (request('creator') || request('assign')),
                fn ($builder) => $builder->status(request('status'), request('creator'), request('assign')),
                fn ($builder) => $builder->orderBy('id', 'desc')
            )
            ->when(request('creator'), fn($builder) => $builder->whereCreator(request('creator')))
            ->when(request('assign'), fn($builder) => $builder->whereAssignTo(request('assign')))
            ->with(['creator', 'assign'])
            ->withCount(['creator', 'assign'])
            ->paginate(20);

        return LeadResource::collection(
            $leads
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() : JsonResource
    {
        abort_unless(auth()->user()->tokenCan('leads.create'),
            Response::HTTP_FORBIDDEN
        );

        $attributes = (new LeadValidator())->validate(
            $lead = new Lead(),
            request()->all()
        );

        $attributes['creator'] = auth()->id();
        $attributes['assign_to'] = request('assign_to');
        $attributes['status'] = Lead::STATUS_NEGOTIATION;

        $lead = Lead::create(
            $attributes
        );

        return LeadResource::make(
            $lead->load(['creator', 'assign'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function show(Lead $lead) : JsonResource
    {
        $lead->load(['creator', 'assign']);
        return LeadResource::make($lead);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function edit(Lead $lead)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function update(Lead $lead) : JsonResource
    {
        abort_unless(auth()->user()->tokenCan('leads.update'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $lead);

        $attributes = (new LeadValidator())->validate($lead, request()->all());

        $lead->update($attributes);

        return LeadResource::make(
            $lead->load(['creator', 'assign'])
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lead $lead)
    {
        abort_unless(auth()->user()->tokenCan('leads.delete'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('delete', $lead);

        $lead->delete();
    }
}
