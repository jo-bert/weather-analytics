<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;

class AlertController extends Controller
{
  //make the controller run with authenticated user and then submit and store to DB. Make sure user can retrigger it
  public function index()
  {
    $alerts = Alert::with(['location' => function ($query) {
      $query->select('id', 'name', 'region', 'country');
    }])->orderBy('id')->get()->map(function ($alert) {
      $alert->full_location = $alert->location->name . ', ' . $alert->location->region . ', ' . $alert->location->country;
      unset($alert->location_id);
      unset($alert->location);
      return $alert;
    });
    return Inertia::render('Locations/Alert', [
      'locations' => Cache::remember('locations', now()->addMinute(), function () {
        return Location::select(['id', 'name', 'country'])->get();
      }),
      'alerts' => $alerts
    ]);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'condition' => 'required|string',
      'expiry' => 'required|date',
      'location_id' => 'required|exists:locations,id',
      'maxValue' => 'nullable|numeric',
      'minValue' => 'nullable|numeric',
      'parameter' => 'required|string',
      'paused' => 'boolean',
      'value' => 'nullable|numeric',
    ]);
    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $alert = Alert::create($request->all());
    if ($alert) {
      return response()->json(Alert::all(), 201);
    } else {
      return response()->json(Alert::all(), 400);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Alert $alert)
  {
    // Validate the request data
    $validator = Validator::make($request->all(), [
      'condition' => 'required|string',
      'expiry' => 'required|date',
      'location_id' => 'required|exists:locations,id',
      'maxValue' => 'nullable|numeric',
      'minValue' => 'nullable|numeric',
      'parameter' => 'required|string',
      'paused' => 'boolean',
      'value' => 'nullable|numeric',

    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    // Update the alert
    $alert->update($request->all());

    // Return the updated alert
    return response()->json($alert, 200);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    try {
      $alert = Alert::findOrFail(id: $id);

      // Delete the alert
      $alert->delete();

      // Return a success message
      return response()->json(['message' => 'Alert deleted'], 200);
    } catch (ModelNotFoundException $e) {
      return response()->json(['errors' => ['Invalid Id']], 400);
    }
  }

  /**
   * Pause the specified alert.
   */
  public function pause(Request $request, $id)
  {
    // Find the alert
    $alert = Alert::findOrFail($id);

    // Pause the alert
    $alert->paused = true;
    $alert->save();

    // Return the updated alert
    return response()->json($alert, 200);
  }

  /**
   * Resume the specified alert.
   */
  public function resume(Request $request, $id)
  {
    // Find the alert
    $alert = Alert::findOrFail($id);

    // Resume the alert
    $alert->paused = false;
    $alert->save();

    // Return the updated alert
    return response()->json($alert, 200);
  }
}
