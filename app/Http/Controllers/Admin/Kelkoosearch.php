<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Shortcode;

class Kelkoosearch extends Controller
{

    public function createShortcode(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required',
            'see_offer_button_text' => 'required',
            'find_out_more_button_text' => 'required',
            'api_key' => 'required',
        ]);

        Shortcode::updateOrCreate(
            ['countryName' => $validated['country']], // condition
            [ // update values
                'see_offer_button_text' => $validated['see_offer_button_text'],
                'find_out_more_button_text' => $validated['find_out_more_button_text'],
                'api_key' => $validated['api_key'],
            ]
        );

        return redirect()->back()->with('success', 'Api key updated successfully.');
    }


    public function addCountry(Request $request)
    {
        $validated = $request->validate([
            'country_name' => 'required|string|max:255',
            'country_code' => 'required|string|max:10',
        ]);

        Country::create([
            'countryname' => $validated['country_name'],
            'countrycode' => $validated['country_code'],
        ]);

        return redirect()->back()->with('success', 'Country added successfully.');
    }


    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return redirect()->back()->with('success', 'Country deleted successfully.');
    }

    public function Apikey(Request $request)
    {
        $config = Shortcode::first(); // get the first (and only) row
        return view('Admin/kalkoosearch/apikey', compact('config'));
    }
}
