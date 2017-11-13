<?php

namespace Tests\Unit;

use App\PendingDhcpAssignment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DhcpReceiptControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function a_valid_dhcp_assignment_is_saved_via_post()
    {
        $this->assertNull(PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')->first());

        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:00',
            'ip_address' => '192.168.100.1',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true ]);

        $this->assertNotNull(
            PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')
                ->whereNull('remote_id')
                ->where('ip_address','=','192.168.100.1')
                ->first()
        );
    }

    /**
     * @test
     */
    public function a_valid_dhcp_assignment_is_saved_via_get()
    {
        $this->assertNull(PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')->first());

        $response = $this->get('/api/dhcp_assignments?leased_mac_address=00:00:00:00:00:00&ip_address=192.168.100.1');

        $response->assertStatus(200)
            ->assertJson(['success' => true ]);

        $this->assertNotNull(
            PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')
                ->whereNull('remote_id')
                ->where('ip_address','=','192.168.100.1')
                ->first()
        );
    }

    /**
     * @test
     */
    public function a_valid_dhcp_assignment_with_a_remote_id_is_saved()
    {
        $this->assertNull(PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')->first());

        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:00',
            'ip_address' => '192.168.100.1',
            'remote_id' => 'AA:AA:AA:AA:AA:AA'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true ]);

        $this->assertNotNull(
            PendingDhcpAssignment::where('leased_mac_address','=','00:00:00:00:00:00')
                ->where('remote_id','=','AA:AA:AA:AA:AA:AA')
                ->where('ip_address','=','192.168.100.1')
                ->first()
        );
    }

    /**
     * @test
     */
    public function a_dhcp_assignment_with_a_bad_mac_is_rejected()
    {
        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:0Z',
            'ip_address' => '192.168.100.1',
        ]);

        $this->assertEquals("The leased mac address must be a valid MAC address.",$response->json()['errors']['leased_mac_address'][0]);
    }

    /**
     * @test
     */
    public function a_dhcp_assignment_with_a_bad_remote_id_is_rejected()
    {
        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:0A',
            'ip_address' => '192.168.100.1',
            'remote_id' => '00:00:00:00:00:0Z',
        ]);

        $this->assertEquals("The remote id must be a valid MAC address.",$response->json()['errors']['remote_id'][0]);
    }

    /**
     * @test
     */
    public function a_dhcp_assignment_with_a_missing_mac_is_rejected()
    {
        $response = $this->json('POST', '/api/dhcp_assignments', [
            'ip_address' => '192.168.100.1',
            'remote_id' => '00:00:00:00:00:0A',
        ]);

        $this->assertEquals("The leased mac address field is required.",$response->json()['errors']['leased_mac_address'][0]);
    }

    /**
     * @test
     */
    public function a_dhcp_assignment_with_a_missing_ip_address_is_rejected()
    {
        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:00',
            'remote_id' => '00:00:00:00:00:0A',
        ]);

        $this->assertEquals("The ip address field is required.",$response->json()['errors']['ip_address'][0]);
    }

    /**
     * @test
     */
    public function a_dhcp_assignment_with_a_bad_ip_is_rejected()
    {
        $response = $this->json('POST', '/api/dhcp_assignments', [
            'leased_mac_address' => '00:00:00:00:00:0A',
            'ip_address' => '192.168.100.256',
        ]);

        $this->assertEquals("The ip address must be a valid IP address.",$response->json()['errors']['ip_address'][0]);
    }
}