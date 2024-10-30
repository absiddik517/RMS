<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Models\Order\Product;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Product\StoreRequest;
use App\Http\Requests\Order\Product\UpdateRequest;
use App\Http\Resources\Order\ProductResource;

class ProductController extends Controller
{
    use Filter;
    public function index(){
      $products = ProductResource::collection($this->getFilterData(Product::class, [
        'like' => ["name", "unit", "rate", "transport_rate"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Order/Product', compact('products', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Product::create($req->validated());
        $toast = [
          'message' => 'Product has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('order.product.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $product = Product::find($id);
        $product->update($req->validated());
        $toast = [
          'message' => 'Product <strong>'.$product->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $product = Product::findOrFail($id);
        if($product->is_sold()){
          $toast = [
            'message' => 'Product <strong>'.$product->name.'</strong> can not delete as it already has sold!', 
            'type' => 'error'
          ];
        }else{
          $product->delete();
          $toast = [
            'message' => 'Product <strong>'.$product->name.'</strong> has <kbd>deleted</kbd> successfull!', 
            'type' => 'success'
          ];
        }
      }catch(\Exception $e){
        $toast = [
          'message' => exception_message($e), 
          'type' => 'error'
        ];
      }
      return redirect()->back()->with('toast', $toast);
    }
}
