<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        //Get single Data
        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }

            return ResponseFormatter::error('Team Not Found', 404);
        }

        // Get Multiple Data
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams Found'
        );
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            // upload icon
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // create teams
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if (!$team) {
                throw new Exception('Team Not Created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            // get team
            $team = Team::find($id);

            // check if team exist
            if (!$team) {
                throw new Exception('Company not Created');
            }

            // upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            //update team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id
            ]);

            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get team
            $team = Team::find($id);

            // Check if team exists
            if (!$team) {
                throw new Exception('Team Not Found');
            }

            // Delete team
            $team->delete();

            return ResponseFormatter::success('Team Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
