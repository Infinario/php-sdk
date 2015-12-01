<?php
namespace Infinario;


class InfinarioTest extends \PHPUnit_Framework_TestCase {

    public function testConstructDefault()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef');
    }

    public function testConstructDebugTrue()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true));
    }

    public function testConstructDebugOther()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => 'xxx'));
    }

    public function testConstructCustomerString()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'customer' => 'xxx'));
    }

    public function testConstructCustomerInteger()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'customer' => 11));
    }

    public function testConstructCustomerFloat()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'customer' => 14.7));
    }

    public function testConstructTransportCheckNull()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'transport' => null));
    }

    public function testConstructTransportCheckInvalidProduction()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => false, 'transport' => 'xxx'));
    }

    public function testConstructTransportCheckInvalidDebug() {
        $this->setExpectedException('\Infinario\Exception');
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'transport' => 'xxx'));
    }

    public function testConstructTokenCheckInvalidProduction()
    {
        new Infinario(123);
    }

    public function testConstructTokenCheckInvalidDebug()
    {
        $this->setExpectedException('\Infinario\Exception');
        new Infinario(123, array('debug' => true));
    }

    public function testConstructTargetCheckInvalidTypeProduction()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => false, 'target' => 123));
    }

    public function testConstructTargetCheckInvalidTypeDebug() {
        $this->setExpectedException('\Infinario\Exception');
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'target' => 123));
    }

    public function testConstructTargetCheckInvalidProduction()
    {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => false, 'target' => 'host.name'));
    }

    public function testConstructTargetCheckInvalidDebug() {
        $this->setExpectedException('\Infinario\Exception');
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'target' => 'host.name'));
    }

    public function testConstructTargetHttp() {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'target' => 'http://api.infinario.com'));
    }

    public function testConstructTargetHttps() {
        new Infinario('12345678-90ab-cdef-1234-567890abcdef', array('debug' => true, 'target' => 'https://api.infinario.com'));
    }

    public function testTrackEventNoIdsDebug() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->never())
            ->method('postAndForget');

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'http://api.infinario.com', 'transport' => $transport));

        $this->setExpectedException('\Infinario\Exception');
        $i->track('test');
    }

    public function testTrackEventNoIdsProduction() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->never())
            ->method('postAndForget');

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('target' => 'http://api.infinario.com', 'transport' => $transport));

        $i->track('test');
    }

    public function testTrackEventHttp() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->equalTo('http://api.infinario.com/crm/events'), $this->equalTo(array(
                'customer_ids' => array('registered' => 12),
                'company_id' => '12345678-90ab-cdef-1234-567890abcdef',
                'type' => 'test',
                'properties' => new \stdClass()
            )));

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'http://api.infinario.com', 'transport' => $transport,
                'customer' => 12));
        $i->track('test');
    }

    public function testTrackEventHttps() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->equalTo('https://api.infinario.com/crm/events'), $this->equalTo(array(
                'customer_ids' => array('registered' => 12),
                'company_id' => '12345678-90ab-cdef-1234-567890abcdef',
                'type' => 'test',
                'properties' => new \stdClass()
            )));

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'https://api.infinario.com', 'transport' => $transport,
                'customer' => 12));
        $i->track('test');
    }

    public function testUpdateCustomerNoIdsDebug() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->never())
            ->method('postAndForget');

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'http://api.infinario.com', 'transport' => $transport));

        $this->setExpectedException('\Infinario\Exception');
        $i->update(array("first_name" => "value"));
    }

    public function testUpdateCustomerNoIdsProduction() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->never())
            ->method('postAndForget');

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('target' => 'http://api.infinario.com', 'transport' => $transport));

        $i->update(array("first_name" => "value"));
    }

    public function testUpdateCustomerHttp() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->equalTo('http://api.infinario.com/crm/customers'), $this->equalTo(array(
                'ids' => array('registered' => 12),
                'company_id' => '12345678-90ab-cdef-1234-567890abcdef',
                'properties' => array("first_name" => "value")
            )));

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'http://api.infinario.com', 'transport' => $transport,
                'customer' => 12));
        $i->update(array("first_name" => "value"));
    }

    public function testUpdateCustomerHttps() {
        $transport = $this->getMockBuilder('Infinario\Transport')->getMock();

        $transport->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->equalTo('https://api.infinario.com/crm/customers'), $this->equalTo(array(
                'ids' => array('registered' => 12),
                'company_id' => '12345678-90ab-cdef-1234-567890abcdef',
                'properties' => array("first_name" => "value")
            )));

        $i = new Infinario('12345678-90ab-cdef-1234-567890abcdef',
            array('debug' => true, 'target' => 'https://api.infinario.com', 'transport' => $transport,
                'customer' => 12));
        $i->update(array("first_name" => "value"));
    }

}
