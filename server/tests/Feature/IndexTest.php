<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use Auth;

class IndexTest extends TestCase
{    
    public function testTerminal()
    {
        $response = $this->get(Route('home'));
        $response->assertStatus(302);
        
        $response = $this->get(Route('login'));
        $response->assertStatus(200);
        
        // Login Admin user
        $user = \App\Http\Models\User::whereAccess(2)->first();
        Auth::login($user);
        
        $response = $this->get(Route('home'));
        $response->assertStatus(200);
        
        // Room
        $room = \App\Http\Models\PlanPartsModel::get()->first();
        
        $response = $this->get(Route('terminal.room', $room->id));
        $response->assertStatus(200);
        
        // Device
        $device = \App\Http\Models\Device::get()->first();
        
        $response = $this->get(Route('terminal.device', $room->id));
        $response->assertStatus(200);
        
        $response = $this->get(Route('terminal.device-changes', -1));
        $response->assertStatus(200);
        
        $response = $this->get(Route('terminal.checked'));
        $response->assertStatus(200);
        
        $response = $this->get(Route('logout'));
        $response->assertStatus(302);
    }
}
