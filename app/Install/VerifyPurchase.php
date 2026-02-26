<?php

namespace App\Install;

use App\Helpers\Classes\ArtisanApi;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class VerifyPurchase
{
    protected $provider = "\150\x74\164\160\x73\72\x2f\x2f\x76\x65\x72\x69\x66\x79\x2e\142\143\x73\164\x61\x74\151\x63\56\143\157\155\x2f\141\x70\151\x2d\x70\x72\x6f\166\x69\144\x65\162";
    protected $product = "\155\x6f\156\x73\x74\x65\162\x2d\164\157\x6f\x6c\163";
    protected $key_path;
    public function __construct()
    {
        goto Db489;
        cbc78:
        e2ff4:
        goto F71ff;
        Ebdc6:
        abort(401, "\x53\x6f\x6d\x65\164\150\x69\156\x67\40\x77\145\x6e\x74\x20\x77\x72\x6f\156\x67\x2c\x20\160\x6c\145\x61\x73\x65\x20\x63\x6f\156\x74\x61\143\164\40\x73\165\x70\160\157\162\164\56");
        goto cbc78;
        Db489:
        if (isset($this->provider)) {
            goto e2ff4;
        }
        goto Ebdc6;
        F71ff:
        $this->key_path = storage_path("\141\160\160\x2f\56" . $this->product);
        goto feafe;
        feafe:
    }
    public function satisfied()
    {
        return app(ArtisanApi::class)->hasRegistered();
    }
    public function authorize()
    {
        goto acea3;
        acea3:
        $authorized = Request::input("\141\165\164\x68\157\x72\x69\172\145\x64");
        goto A30a3;
        Dce0b:
        if ($authorized === "\163\x75\143\x63\145\163\163" && $authorized_key) {
            goto a360d;
        }
        goto b787a;
        f65f7:
        $authorized_key = Request::input("\x61\x75\164\150\x6f\162\151\x7a\145\x64\137\x6b\145\x79", null);
        goto Dce0b;
        A30a3:
        $message = Request::input("\x6d\145\163\x73\141\x67\x65");
        goto f65f7;
        b787a:
        return redirect("\x2f\x69\x6e\x73\164\x61\154\x6c\57\166\145\162\151\x66\171")->withErrors($message);
        goto c87b1;
        Bf417:
        a360d:
        goto e45c0;
        c87b1:
        goto c65c1;
        goto Bf417;
        D9f92:
        c65c1:
        goto Ae6f6;
        e45c0:
        return $this->generate_key($authorized_key, $message);
        goto D9f92;
        Ae6f6:
    }
    public function login()
    {
        $redirect = $this->provider . "\x3f\151\x74\145\x6d\x3d" . $this->product . "\x26\162\145\x74\x75\x72\x6e\x5f\x75\162\151\75" . urlencode(URL::route("\166\x65\162\x69\146\x79\x2e\x72\145\164\x75\x72\x6e"));
        return Redirect::away($redirect);
    }
    protected function generate_key($code, $message)
    {
        goto fe39f;
        d4aa8:
        Storage::disk("\154\157\x63\x61\x6c")->put($filename, artisanCrypt()->encrypt($code));
        goto Ef92a;
        C2f9a:
        $filename = "\x2e" . $this->product;
        goto bec35;
        Ef92a:
        D3bda:
        goto D8abb;
        D8abb:
        return redirect("\x2f\x69\x6e\163\164\141\x6c\x6c\57\x76\x65\162\151\146\x79")->withSuccess($message);
        goto c7db9;
        fe39f:
        if ($this->satisfied()) {
            goto D3bda;
        }
        goto C2f9a;
        bec35:
        Session::put("\x70\165\x72\143\x68\x61\x73\x65\x5f\x63\x6f\144\x65", $code);
        goto d4aa8;
        c7db9:
    }
}
