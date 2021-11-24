<?php

namespace App\Http\Controllers\API\V1;

use App\Models\{User, Lead};
use App\Models\Validators\LeadValidator;
use App\Http\Resources\V1\LeadResource;
use App\Notifications\{AlertAdminWhenLeadsIsAdded, AlertAdminWhenLeadsIsUpdated};
use Illuminate\Http\{Request, Response};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\{Rule, ValidationException};
use Illuminate\Support\Facades\{DB, Notification, Storage};

class LeadController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/leads",
     *      operationId="index",
     *      tags={"Leads"},
     *      summary="Get list of all leads",
     *      security={{"bearerAuth":{}}},
     *      description="Returns list of leads",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */

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
            ->when(request('status') && auth()->user(),
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
     * @OA\Post(
     ** path="/api/v1/leads",
     *   tags={"Leads"},
     *   summary="Create a Leads",
     *   operationId="create",
     *   security={{"bearerAuth":{}}},
     * 
     *  @OA\RequestBody(
     *      required=true,
     *      description="pass user Credential",
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              required={"title", "description", "value", "source", "contact_person", "contact_email", "contact_phone", 
     *                      "contact_organization", "start_date", "end_date", "status"
     *              },
     *              @OA\Property(property="title", type="string", example="enter title"),
     *              @OA\Property(property="description", type="string", example="enter description"),
     *              @OA\Property(property="value", type="integer", example="9000000"),
     *              @OA\Property(property="source", type="string", example="Linkedin"),
     *              @OA\Property(property="contact_person", type="string", example="Afeez"),
     *              @OA\Property(property="contact_email", type="string", example="afeez@yahoo.com"),
     *              @OA\Property(property="contact_phone", type="string", example="+2349061234555"),
     *              @OA\Property(property="contact_organization", type="string", example="Aliami & Co Ltd"),
     *              @OA\Property(property="start_date", type="date", example="2021-11-21"),
     *              @OA\Property(property="end_date", type="date", example="2021-12-21"),
     *              @OA\Property(property="status", type="string", example="In Progress"),
     *          ),
     *     ),
     *  ),
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *  )
     **/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() : JsonResource
    {
        abort_unless(auth()->user()->tokenCan('leads.create'),
            Response::HTTP_FORBIDDEN,
            'You have to be authenticated'
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
        $user = auth()->user();

        Notification::send(User::where('is_admin', true)->get(), new AlertAdminWhenLeadsIsAdded($user, $lead));

        return LeadResource::make(
            $lead->load(['creator', 'assign'])
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/leads/{id}",
     *      operationId="show",
     *      tags={"Leads"},
     *      summary="Get leads information",
     *      description="Returns leads data",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Leads id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */

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
     * @OA\Put(
     *      path="/api/v1/leads/{id}",
     *      operationId="update",
     *      tags={"Lead"},
     *      summary="Update existing leads",
     *      description="Returns updated leads data",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Lead id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
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
        $user = auth()->user();

        Notification::send(User::where('is_admin', true)->get(), new AlertAdminWhenLeadsIsUpdated($user, $lead));

        return LeadResource::make(
            $lead->load(['creator', 'assign'])
        );
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/leads/{id}",
     *      operationId="delete",
     *      tags={"Lead"},
     *      summary="Delete existing leads",
     *      description="Deletes a record and returns no content",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Lead id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */

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

