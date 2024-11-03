<?php

namespace App\Http\Controllers\Company;

use Exception;
use App\Models\Season;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\Season\StoreRequest;
use App\Http\Requests\Company\Season\UpdateRequest;
use App\Http\Resources\Company\SeasonResource;

class SeasonController extends Controller
{
    use Filter;
    public function index(){
      $seasons = SeasonResource::collection($this->getFilterData(Season::class, [
        'like' => ["title", "start", "end"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Company/Season', compact('seasons', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Season::create($req->validated());
        $toast = [
          'message' => 'Season has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('company.season.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $season = Season::find($id);
        $season->update($req->validated());
        $toast = [
          'message' => 'Season <strong>'.$season->name.'</strong> has <kbd>updated</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => exception_message($e), 
          'type' => 'error'
        ];
      }
      return redirect()->back()->with('toast', $toast);
    }
    
    public function destroy($id){
      //sleep(5);
      try{
        $season = Season::findOrFail($id);
        $season->delete();
        $toast = [
          'message' => 'Season <strong>'.$season->name.'</strong> has <kbd>deleted</kbd> successfull!', 
          'type' => 'success'
        ];
      }catch(\Exception $e){
        $toast = [
          'message' => exception_message($e), 
          'type' => 'error'
        ];
      }
      return redirect()->back()->with('toast', $toast);
    }
}
