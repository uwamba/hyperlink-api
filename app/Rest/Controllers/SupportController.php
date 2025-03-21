<?php

namespace App\Rest\Controllers;

    
    use App\Models\Support;
    use App\Rest\Resources\SupportResource;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use App\Rest\Controller as RestController;
    use Illuminate\Validation\ValidationException;
    use Exception;
    



class SupportController extends RestController
{
    

        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            return SupportResource::collection(Support::all());
        }
    
        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {
            $data = $request->validate([
                'client_id' => 'required|string|max:255',
                'email' => 'required|email|unique:clients,email',
                'description' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ]);
    
            $client = Support::create($data);
            return new SupportResource($client);
    
    
        }
    
        /**
         * Display the specified resource.
         */
        public function show(Support $support)
        {
            return new SupportResource($support);
        }
    
        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, Support $support)
        {
            $validated = $request->validate([
                'client_id' => 'sometimes|integer|exists:clients,id',
                'email' => 'sometimes|email',
                'description' => 'sometimes|string',
                'address' => 'nullable|string',
            ]);
    
            $support->update($validated);
    
            return new SupportResource($support);
        }
    
        /**
         * Remove the specified resource from storage.
         */
       
    }
    