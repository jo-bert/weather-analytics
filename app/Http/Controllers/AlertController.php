<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\Location;

class AlertController extends Controller
{
  //make the controller run with authenticated user and then submit and store to DB. Make sure user can retrigger it
  public function index()
  {
    $locations = Location::select(['id', 'name', 'country'])->get();
    $alerts = Alert::all();
    return Inertia::render('Locations/Alert', [
      'locations' => $locations,
      'alert' => $alerts
    ]);
  }

  public function store(Request $request)
  {
    // Validate the request data
    $validator = Validator::make($request->all(), [
      'location_id' => 'required',
      'parameter' => 'required|string',
      'condition' => 'required|string',
      'value' => 'nullable|numeric',
      'minValue' => 'nullable|numeric',
      'maxValue' => 'nullable|numeric',
      'expiry' => 'required|date',
      'paused' => 'boolean',
    ]);
    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    // Create a new alert
    $alert = Alert::create($request->all());

    if ($alert) {
      // Return the new alert
      return response()->json($alert, 201);
    } else {
      return response()->status(200);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Alert $alert)
  {
    // Validate the request data
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|exists:users,id',
      'location_id' => 'required|exists:locations,id',
      'parameter' => 'required|string',
      'condition' => 'required|string',
      'value' => 'nullable|numeric',
      'minValue' => 'nullable|numeric',
      'maxValue' => 'nullable|numeric',
      'expiry' => 'required|date',
      'paused' => 'boolean',
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
  public function destroy(Request $request, $id)
  {
    // Find the alert
    $alert = Alert::findOrFail($id);

    // Delete the alert
    $alert->delete();

    // Return a success message
    return response()->json(['message' => 'Alert deleted'], 200);
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
