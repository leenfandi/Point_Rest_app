<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryRestaurant;
use App\Models\EmployeeRestaurant;
use App\Models\Meal;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Role;
use App\Models\Rusturant;
use App\Models\Service;
use App\Models\Tabel;
use App\Models\User;
use App\Traits\mealsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Hash;


class EmployeeController extends Controller
{
  use mealsTrait;

    public function __construct()
    {
      $this->middleware('authEmployee')->except('login','getImage');
      $this->middleware('authRestaurantEmployee')->except('login');
    }

     //////////// login
    public function login(Request $request){
        $validate=Validator::make($request->all(),
        [
          'password'=>'required|min:8',
          'email'=>'required|exists:users|string|email|max:255',
        ]);

        if($validate->fails()){
                    return response()->json($validate->errors(),422);
        }
        if (!auth()->validate($request->only('email','password')) )
        {
          return response()->json(['message'=>'failed password'],400);
        }
        $user = User::where('email' , $request->email)->first();
        $role='';
        if($user->hasRole('employee')){
          $role='employee';
        }
        if($user->hasRole('manager')){
          $role='manager';
        }
        if($user->hasRole('admin')){
          $role='admin';
        }
        $token = auth()->login($user);
        return response()->json([
            'message' => 'login successfully',
            'data' => $user,
            'Role' => $role,
            'token'=>$token
        ],200);
    }


////// add Category
  public function addCategory(Request $request){
    $validate=Validator::make($request->all(),
    [

       'name'=>'required|string',
       'type'=>'required|string',
    ]);

   if($validate->fails()){
    return  response()->json($validate->errors(), 400);
   }
   $user=$request->user();
   $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
    $category=Category::create(['name'=>$request->name,'type'=>$request->type]);
    $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
    CategoryRestaurant::create(['restaurant_id'=>$restaurant->id,'category_id'=>$category->id]);
    return response()->json(['message'=>'created successfully','data'=>$category],200);
  }

////////// update Category
  public function updateCategory(Request $request){
    $validate=Validator::make($request->all(),
    [
       'category_id'=>'required|exists:categories,id',
       'name'=>'required|string',
       'type'=>'required|string',
    ]);

   if($validate->fails()){
    return  response()->json($validate->errors(), 400);
    }
    $category= Category::find($request->category_id);
    $category->update($request->only('name','type'));
    $category->save();
    return response()->json(['message'=>'updated successfully','data'=>$category],200);
  }

  ///////////////// delete Category
  public function deleteCategory(Request $request){
    $validate=Validator::make($request->all(),
    [
       'category_id'=>'required|exists:categories,id',
    ]);

    if($validate->fails()){
     return  response()->json($validate->errors(), 400);
    }
    $del=CategoryRestaurant::where('category_id',$request->category_id);
    $del->delete();
    $category= Category::find($request->category_id);
    $category->delete();
    return response()->json(['message'=>'deleted successfully'],200);
  }

///////// display Category
  public function displayCategory(Request $request){
    $user=$request->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
   $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
   $group=CategoryRestaurant::where('restaurant_id',$restaurant->id)->get();
   if($group){
    $ids = [];
    foreach ($group as $item) {
        $ids[] = $item->category_id;
    }
    if($request->type)
    $category= Category::whereIn('id',$ids)->where('type',$request->type)->get();
    else
    $category= Category::whereIn('id',$ids)->get();
    return response()->json(['message'=>'fetched successfully','data'=>$category],200);
    }
    return response()->json(['message'=>'no categories yet'],200);
 }



///////// add_meal
public function store_meal (Request $request){

  $validate=Validator::make($request->all(),
  [
     'name'=>'required|string',
     'description'=>'required|string',
     'image'=>'mimes:jpg,jpeg,png|required',
     'category_id'=>'required|exists:categories,id',
     'price'=>'required',
  ]);

    if($validate->fails()){
      return  response()->json($validate->errors(), 400);
    }
    $user=$request->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
      $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
      $file_name = $this->saveImage($request -> image ,'images/meals');
      $imageUrl='http://192.168.43.98:8000/api/getImage?name='.$file_name;
      $category=Category::find($request->category_id);
      $meal= Meal::create([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $imageUrl,
        'category_id' => $request->category_id,
        'rustaurant_id' => $restaurant->id,
        'price' => $request->price,
        'sales_count' => 0,
        'type' => $category->type,
        'menu' => true,
        ]);
      return  response()->json($meal, 200);

}

/////// update_meal
public function update_meal (Request $request){

      $validate=Validator::make($request->all(),
      [
        'meal_id'=>'required|exists:meals,id',
        'name'=>'required|string',
        'description'=>'required|string',
        'image'=>'mimes:jpg,jpeg,png|required',
        'price'=>'required',
      ]);

    if($validate->fails()){
      return  response()->json($validate->errors(), 400);
    }

    $meal= Meal::find($request->meal_id);
    if($meal->image)
    {
      FacadesFile::delete(public_path("images/$meal->image"));
    }
    $file_name = $this->saveImage($request -> image ,'images/meals');
    $meal->update([ 'image' => $file_name]);
      $meal->update([
      'name'=>$request->name,
      'description'=>$request->description,
      'image'=>$file_name,
      'price'=>$request->price,]);
      return  response()->json($meal, 200);

}

/////////// delete meal
public function delete_meal (Request $request){

    $validate=Validator::make($request->all(),
    [
      'meal_id'=>'required|exists:meals,id',
    ]);

  if($validate->fails()){
    return  response()->json($validate->errors(), 400);
  }

  $meal= Meal::find($request->meal_id);
  if($meal->image)
  {
    FacadesFile::delete(public_path("images/$meal->image"));
  }
    $meal->delete();
    return  response()->json("deleted sucsess", 200);

}

/////////// add menu
public function addMenu (Request $request){

  $validate=Validator::make($request->all(),
  [
    'meal_id'=>'required|exists:meals,id',
  ]);

  if($validate->fails()){
    return  response()->json($validate->errors(), 400);
  }

    $meal= Meal::find($request->meal_id);
    $meal->menu=true;
    $meal->save();
    return  response()->json(['message'=>"updated successfully",'data'=>$meal], 200);

}
/////////// drop menu
public function dropMenu (Request $request){

  $validate=Validator::make($request->all(),
  [
    'meal_id'=>'required|exists:meals,id',
  ]);

  if($validate->fails()){
    return  response()->json($validate->errors(), 400);
  }
    $meal= Meal::find($request->meal_id);
    $meal->menu=false;
    $meal->save();
    return  response()->json(['message'=>"updated successfully",'data'=>$meal], 200);

}

///////// display menu
public function displayMenu (Request $request){
  if($request->menu==0||$request->menu){
    $user=$request->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
    $rest=Rusturant::where('id',$employee->rustaurant_id)->first();
    if($request->type){
      $categoryres=CategoryRestaurant::where('restaurant_id',$rest->id)->get();
      $ides=[];
      for ($i=0; $i <count($categoryres) ; $i++) { 
        $ides[]=$categoryres[$i]->category_id;
      }
      $categorys=Category::whereIn('id',$ides)->where('type',$request->type)->get();
      $ids=[];
      for ($i=0; $i <count($categorys) ; $i++) { 
     $ids[]=$categorys[$i]->id;
      }
      $meals= Meal::where('menu',$request->menu)->where('rustaurant_id',$rest->id)->whereIn('category_id',$ids)->orderBY('category_id','desc')->get();
    }
    else
    $meals= Meal::where('menu',$request->menu)->where('rustaurant_id',$rest->id)->orderBY('category_id','desc')->get();
    return response()->json(['message'=>'fetched successfully','data'=>$meals],200);
  }
 $user=$request->user();
 $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
 $rest=Rusturant::where('id',$employee->rustaurant_id)->first();

 if($request->type){
  $categoryres=CategoryRestaurant::where('restaurant_id',$rest->id)->get();
  $ides=[];
  for ($i=0; $i <count($categoryres) ; $i++) { 
    $ides[]=$categoryres[$i]->category_id;
  }
  $categorys=Category::whereIn('id',$ides)->where('type',$request->type)->get();
  $ids=[];
  for ($i=0; $i <count($categorys) ; $i++) { 
 $ids[]=$categorys[$i]->id;
  }
  $meals= Meal::where('menu',true)->where('rustaurant_id',$rest->id)->whereIn('category_id',$ids)->orderBY('category_id','desc')->get();
}else
 $meals= Meal::where('menu',true)->where('rustaurant_id',$rest->id)->orderBY('category_id','desc')->get();
 return response()->json(['message'=>'fetched successfully','data'=>$meals],200);
}
///////// display meals
public function displayMeals (Request $request){
  $user=$request->user();
  $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
  $rest=Rusturant::where('id',$employee->rustaurant_id)->first();
  if($request->type){
    $categoryres=CategoryRestaurant::where('restaurant_id',$rest->id)->get();
    $ides=[];
    for ($i=0; $i <count($categoryres) ; $i++) { 
      $ides[]=$categoryres[$i]->category_id;
    }
    $categorys=Category::whereIn('id',$ides)->where('type',$request->type)->get();
    $ids=[];
    for ($i=0; $i <count($categorys) ; $i++) { 
   $ids[]=$categorys[$i]->id;
    }
    $meals= Meal::where('rustaurant_id',$rest->id)->whereIn('category_id',$ids)->orderBY('category_id','desc')->get();
  }else
  $meals= Meal::where('rustaurant_id',$rest->id)->orderBY('category_id','desc')->get();
  return response()->json(['message'=>'fetched successfully','data'=>$meals],200);
}

///////// add offer
public function addOffer (Request $request){
      $validate=Validator::make($request->all(),
      [
        'new_price'=>'required',
        'description'=>'required|string',
        'expirate_date'=>'required|date|after:'.now(),
        'meale_ids' => 'required',
        'meale_ids.*' => 'exists:meals,id',
      ]);
      if($validate->fails()){
      return  response()->json($validate->errors(), 400);
      }
      $imageUrl=null;
      if($request->image){
        $file_name = $this->saveImage($request -> image ,'images/meals');
        $imageUrl='http://192.168.43.98:8000/api/getImage?name='.$file_name;
      }
      $user=$request->user();
      $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
      $rest=Rusturant::where('id',$employee->rustaurant_id)->first();
      $ids=$request->meale_ids;
      $oldprice=0;
      for ($i=0; $i < count($ids) ; $i++) {
        $meal=Meal::find($ids[$i]);
        $oldprice+=$meal->price;
      }
      $offer= Offer::create([
        'new_price'=>$request->new_price,
        'old_price'=>$oldprice,
        'description'=>$request->description,
        'expirate_date'=>$request->expirate_date,
        'state'=>true,
        'image'=> $imageUrl,
        'rustaurant_id'=>$rest->id,
      ]);

      for ($i=0; $i < count($ids) ; $i++) {
        OfferItem::create(['offer_id' => $offer->id,'meal_id'=>$ids[$i]]);
      }

    return response()->json(['message'=>'created successfully','data'=>$offer],200);
}

///////// delete offer
public function deleteOffer (Request $request){
    $validate=Validator::make($request->all(),
    [
      'offer_id'=>'required|exists:offers,id',
    ]);
    if($validate->fails()){
    return  response()->json($validate->errors(), 400);
    }
    $offer= Offer::find($request->offer_id);
    $offeritem=OfferItem::where('offer_id',$offer->id)->get();
    for ($i=0; $i < count($offeritem) ; $i++) {
    $offerite= OfferItem::where('id',$offeritem[$i]->id);
    $offerite->delete();
    }
  $offer->delete();
  return response()->json(['message'=>'deleted successfully'],200);
}

///////// active offer
public function activeOffer (Request $request){
    $validate=Validator::make($request->all(),
    [
      'offer_id'=>'required|exists:offers,id',
      'expirate_date'=>'required|date|after:'.now(),
    ]);
    if($validate->fails()){
    return  response()->json($validate->errors(), 400);
    }
    $offer= Offer::find($request->offer_id);
    $offer->state=true;
    $offer->expirate_date=$request->expirate_date;
    $offer->save();
    return response()->json(['message'=>'updated successfully','data'=>$offer],200);
}

///////// unactive offer
public function unactiveOffer (Request $request){
  $validate=Validator::make($request->all(),
  [
    'offer_id'=>'required|exists:offers,id',
  ]);
  if($validate->fails()){
  return  response()->json($validate->errors(), 400);
  }
  $offer= Offer::find($request->offer_id);
  $offer->state=false;
  $offer->save();
  return response()->json(['message'=>'updated successfully','data'=>$offer],200);
}

///////// display offers
public function displayOffers (Request $request){
    $user=auth()->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
  $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
  if($request->active!=null){
    if($request->active==0)
    $offer= Offer::latest()->where('rustaurant_id',$restaurant->id)->where('state',0)->get();
   
    if($request->active==1)
    $offer= Offer::latest()->where('rustaurant_id',$restaurant->id)->where('state',1)->get();
   
  }else
  $offer= Offer::latest()->where('rustaurant_id',$restaurant->id)->get();
  return response()->json(['message'=>'selected successfully','data'=>$offer],200);
}

/////// get Image
public function getImage (Request $request){
  return  response()->download(public_path("images/meals/$request->name"), $request->name);
}

 ///// add table 
 public function addTable(Request $request)
 {
     $validate=Validator::make($request->all(),
   [
     'table_number'=>'required',
     'floor_number'=>'required',
     'chairs_number'=>'required',
   ]);
   if($validate->fails()){
   return  response()->json($validate->errors(), 400);
   }
   $user=auth()->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
  $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
  $tabel= Tabel::create([
     'table_number'=>$request->table_number,
     'floor_number'=>$request->floor_number,
     'rustaurant_id'=>$restaurant->id,
     'chairs_number'=>$request->chairs_number,
     'state'=> 1
   ]);
   return response()->json(['message'=>'created successfully','data'=>$tabel],200);
 }

///// update table 
public function updateTable(Request $request)
 {
     $validate=Validator::make($request->all(),
   [
     'table_number'=>'required',
     'floor_number'=>'required',
     'table_id'=>'required|exists:tabels,id',
     'state'=> 'required',
     'chairs_number'=>'required'
   ]);
   if($validate->fails()){
   return  response()->json($validate->errors(), 400);
   }

  $tabel= Tabel::find($request->table_id);
  $tabel->update([
     'table_number'=>$request->table_number,
     'floor_number'=>$request->floor_number,
     'chairs_number'=>$request->chairs_number,
     'state'=> $request->state
   ]);
   return response()->json(['message'=>'created successfully','data'=>$tabel],200);
 }


 /// mostSold
 public function mostSold(Request $request){
  $user=auth()->user();
    $employee=EmployeeRestaurant::where('user_id',$user->id)->first();
  $restaurant=Rusturant::where('id',$employee->rustaurant_id)->first();
   if($request->type=='meal'){
    $mostSoldMeal = Meal::where('rustaurant_id',$restaurant->id)->where('type','meal')->orderByDesc('sales_count')
 ->first();
   }
 else if($request->type=='drink'){
    $mostSoldMeal = Meal::where('rustaurant_id',$restaurant->id)->where('type','drink')->orderByDesc('sales_count')
  ->first();
  }else{
    $mostSoldMeal = Meal::where('rustaurant_id',$restaurant->id)->orderByDesc('sales_count')
    ->first();
  }
  return response()->json(['message'=>'fetched successfully','data'=>$mostSoldMeal],200);

}
     /// logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'logout successfully'], 200);
    }




}
