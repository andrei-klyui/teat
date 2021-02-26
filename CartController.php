<?php

namespace App\Http\Controllers;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductElectric;
use Illuminate\Http\Request;
use App\Mail\MailClass;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{

    public function view(Request $request)
    {
        $cart = session()->get('cart');
        $cart_s = session()->get('cart_s');
        if (empty($cart) && empty($cart_s)) {
            return view('service.korzina_empty');
        } else {
            return view('service.korzina', ['cart' => $cart, 'cart_s' => $cart_s]);
        }
    }

    public function addToCart(Request $request)
    {
        $id = $request->get('product_id');
        $cart = session()->get('cart');

        if($id) {
            $product = ProductElectric::find($id);
            if (!$cart) {
                $cart = [
                    $id => [
                        "id" => $product->id,
                        "name" => $product->name_description,
                        "quantity" => 1,
                        "price" => $product->price,
                        "price10" => $product->price10,
                        "url" => $product->url,
                        "photo" => $product->photo
                    ]
                ];
            } elseif (isset($cart[$id])) {
                $cart[$id]['quantity']++;
            } else {
                $cart[$id] = [
                    "id" => $product->id,
                    "name" => $product->name_description,
                    "quantity" => 1,
                    "price" => $product->price,
                    "price10" => $product->price10,
                    "url" => $product->url,
                    "photo" => $product->photo
                ];
            }
            session()->put('cart', $cart);
        }

        return '{"status":"ok"}';
    }

    public function addToCart_s(Request $request)
    {
        $id = $request->get('product_id');
        $cart_s = session()->get('cart_s');

        if($id) {
            $product = Product::find($id);
            if ($product) {
                if (!$cart_s) {
                    $cart_s = [
                        $id => [
                            "id" => $product->id,
                            "name" => $product->name_description,
                            "quantity" => 1,
                            "price" => $product->price,
                            "price10" => $product->price10,
                            "url" => $product->url,
                            "photo" => $product->photo
                        ]
                    ];
                } elseif (isset($cart_s[$id])) {
                    $cart_s[$id]['quantity']++;
                } else {
                    $cart_s[$id] = [
                        "id" => $product->id,
                        "name" => $product->name_description,
                        "quantity" => 1,
                        "price" => $product->price,
                        "price10" => $product->price10,
                        "url" => $product->url,
                        "photo" => $product->photo
                    ];
                }
                session()->put('cart_s', $cart_s);
            }
        }

        return '{"status":"ok"}';
    }

    public function changeAmount(Request $request)
    {
        $id = $request->get('product_id');
        $productType = $request->get('product_type');
        $amount = $request->get('amount');
        $cart = session()->get('cart');

        if ($productType === Favorite::TYPE_ELECTRICITY && $cart) {
            $cart[$id]['quantity'] = $amount;
            session()->put('cart', $cart);
        }

        $cart_s = session()->get('cart_s');

        if ($productType === Favorite::TYPE_PLUMBING && $cart_s) {
            $cart_s[$id]['quantity'] = $amount;
            session()->put('cart_s', $cart_s);
        }

        return '{"status":"ok"}';
    }

    public function remove(Request $request)
    {
        $id = $request->get('id');
        if (session()->has('cart')) {
            $cart = session()->get('cart');
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        if (session()->has('cart_s')) {
            $cart_s = session()->get('cart_s');
            unset($cart_s[$id]);
            session()->put('cart_s', $cart_s);
        }
        session()->flash('success', 'Послугу або товар выдалено з корзини!');
        return back();
    }

    public function mail(Request $request){

        $cart = session()->get('cart', []);
        $cart_s = session()->get('cart_s', []);
        $data = array(
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'phone' => $request->post('phone'),
            'street' => $request->post('street'),
            'state' => $request->post('state'),
            'zip' => $request->post('zip'),
            'cart' => $cart,
            'cart_s' => $cart_s
        );

        Mail::send(['html' => 'layouts.cart_mail'], $data, function($message) use ($request) {
            $message->to('levcenkovdomalab@gmail.com', 'Vdomalad')->subject
            ('Замовлення з сайту (корзина без оплати)');
            $message->to( $request->input('email') );
            $message->from('levcenkovdomalab@gmail.com','Vdomalad');
        });

        session()->remove('cart');
        session()->remove('cart_s');
        return redirect()->back()->withSuccess( 'Дякуємо за Ваше замовлення!' );
    }

    public function toggle(Request $request)
    {
        $id = $request->get('product_id');
        $productType = $request->get('product_type');

        if($id && $productType === Favorite::TYPE_PLUMBING) {
            $product = Product::find($id);
            if ($product) {
                $cart_s = session()->get('cart_s');
                if (!$cart_s) {
                    $cart_s = [
                        $id => [
                            "id" => $product->id,
                            "name" => $product->name_description,
                            "quantity" => 1,
                            "price" => $product->price,
                            "price10" => $product->price10,
                            "url" => $product->url,
                            "photo" => $product->photo
                        ]
                    ];
                } elseif (isset($cart_s[$id])) {
                    $cart_s[$id]['quantity']++;
                } else {
                    $cart_s[$id] = [
                        "id" => $product->id,
                        "name" => $product->name_description,
                        "quantity" => 1,
                        "price" => $product->price,
                        "price10" => $product->price10,
                        "url" => $product->url,
                        "photo" => $product->photo
                    ];
                }
                session()->put('cart_s', $cart_s);
            }
        } elseif ($id && $productType === Favorite::TYPE_ELECTRICITY) {
            $product = ProductElectric::find($id);
            $cart = session()->get('cart');
            if (!$cart) {
                $cart = [
                    $id => [
                        "id" => $product->id,
                        "name" => $product->name_description,
                        "quantity" => 1,
                        "price" => $product->price,
                        "price10" => $product->price10,
                        "url" => $product->url,
                        "photo" => $product->photo
                    ]
                ];
            } elseif (isset($cart[$id])) {
                $cart[$id]['quantity']++;
            } else {
                $cart[$id] = [
                    "id" => $product->id,
                    "name" => $product->name_description,
                    "quantity" => 1,
                    "price" => $product->price,
                    "price10" => $product->price10,
                    "url" => $product->url,
                    "photo" => $product->photo
                ];
            }
            session()->put('cart', $cart);
        }

        return '{"status":"ok"}';
    }
}
