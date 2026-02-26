<?php

namespace App\Helpers\Classes;

use Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Helpers\Classes\UpdatesManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArtisanApi extends UpdatesManager
{
    public function register(Request $request)
    {
        goto A4a72;
        A4a72:
        $request->validate(["\143\x6f\144\145" => "\162\x65\x71\x75\x69\x72\145\x64\x7c\165\165\151\x64"], ["\143\x6f\144\x65\x2e\x2a" => "\x50\154\145\141\163\145\40\x65\x6e\x74\145\162\40\x76\141\154\151\x64\40\x70\165\162\x63\150\x61\x73\145\40\x63\157\144\x65\56"]);
        goto D1d49;
        A7a10:
        try {
            goto a65cd;
            eb2bf:
            D35ba:
            goto d685e;
            ffd42:
            $jsonData = $response->json();
            goto E89c0;
            F91dc:
            Storage::disk("\154\x6f\x63\141\154")->put("\56{$this->product}", $content);
            goto eb2bf;
            C559b:
            Setting::set("\160\165\x72\x63\x68\141\163\x65\137\x63\x6f\x64\145", $code);
            goto d39d4;
            bf549:
            $code = $request->input("\x63\157\x64\x65");
            goto F337e;
            E89c0:
            if (!(isset($jsonData["\163\164\x61\x74\165\x73"]) && $jsonData["\x73\164\x61\164\x75\163"] === true)) {
                goto D35ba;
            }
            goto bf549;
            d39d4:
            Setting::save();
            goto F91dc;
            a65cd:
            $response = Http::post($this->register_endpoint, $data);
            goto ffd42;
            F337e:
            try {
                $content = artisanCrypt()->encrypt($code);
            } catch (\Exception $e) {
                throw new \Exception("\x43\157\165\154\x64\x6e\x27\x74\x20\x72\x65\x67\151\163\164\145\162\x20\160\x72\157\144\165\143\x74\54\x20\160\154\x65\x61\x73\145\40\143\x6f\156\x74\x61\x63\164\x20\x73\x75\x70\160\x6f\162\164\56");
            }
            goto C559b;
            d685e:
            return response()->json($jsonData, 200);
            goto Ec1c7;
            Ec1c7:
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        goto F0286;
        D1d49:
        $data = $this->verifyData;
        goto A872b;
        F0286:
        return false;
        goto e4cab;
        A872b:
        $data["\143\157\144\145"] = $request->input("\x63\x6f\144\x65");
        goto A7a10;
        e4cab:
    }
    public function verify()
    {
        goto aae02;
        aae02:
        $file = storage_path("\141\160\x70\57\56{$this->product}");
        goto c2c4d;
        B5fbd:
        try {
            $code = artisanCrypt()->decrypt($content);
        } catch (\Exception $e) {
            $code = null;
        }
        goto De24f;
        ef6d0:
        A1477:
        goto a6f67;
        c2c4d:
        if (!file_exists($file)) {
            goto ec865;
        }
        goto f5bb7;
        Fc647:
        return $code;
        goto c8047;
        a6f67:
        return null;
        goto E84c4;
        bab47:
        goto A1477;
        goto D25ef;
        f5bb7:
        $content = File::get($file);
        goto B5fbd;
        Dd42b:
        return $code;
        goto ef6d0;
        E84c4:
        ec865:
        goto F5309;
        D25ef:
        cfc5b:
        goto Dd42b;
        De24f:
        if (config("\x61\x72\x74\x69\x73\141\x6e\56\151\156\163\164\141\x6c\154\145\144") && setting("\160\x75\x72\x63\x68\141\x73\145\137\x63\x6f\144\145", null) === $code) {
            goto cfc5b;
        }
        goto A5cac;
        A5cac:
        if (config("\x61\x72\x74\x69\163\x61\x6e\56\x69\x6e\x73\x74\x61\x6c\x6c\x65\x64")) {
            goto A77fe;
        }
        goto Fc647;
        c8047:
        A77fe:
        goto bab47;
        F5309:
    }

    public function getToken()
    {
        $code = $this->verify();
        return !empty($code) ? base64_encode($code) : null;
    }

    public function hasRegistered()
    {
        $verify = Validator::make(["\x63\157\x64\145" => $this->verify()], ["\x63\x6f\x64\x65" => "\162\x65\161\x75\x69\x72\x65\x64\174\165\165\151\x64"]);
        return $verify->passes();
    }
}
