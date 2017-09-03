<?php

use TiGR\Form\Element\Dropdown;
use TiGR\Form\Form;

class FormElementDropdownTest extends PHPUnit_Framework_TestCase
{
    public function testRequiredValueIsEmpty()
    {
        $form = $this->getForm(['list' => '']);
        $form->addElement($this->getElement(true));
        $this->assertFalse($form->proceed());
    }

    public function testRequiredValueIsUndefined()
    {
        $form = $this->getForm([]);
        $form->addElement($this->getElement(true));
        $this->assertFalse($form->proceed());
    }

    public function testRequiredValueIsInvalid()
    {
        $form = $this->getForm(['list' => 'Boo']);
        $form->addElement($this->getElement(true));
        $this->assertFalse($form->proceed());
    }

    public function testRequiredValueIsValid()
    {
        $form = $this->getForm(['list' => '1']);
        $form->addElement($this->getElement(true));
        $this->assertTrue($form->proceed());
    }

    public function testOptionalValueIsEmpty()
    {
        $form = $this->getForm(['list' => '']);
        $form->addElement($this->getElement(false));
        $this->assertTrue($form->proceed());
    }

    /**
     * @param $required
     * @return Dropdown
     */
    private function getElement($required)
    {
        return new Dropdown('list', 'List', [
            '' => 'Please select',
            '1' => 'One',
            2 => 'Two'
        ], $required);
    }

    /**
     * @param $value
     * @return Form
     */
    private function getForm($value)
    {
        $form = $this->getMockBuilder('TiGR\Form\Form')->setMethods(['getRequestArray'])->getMock();
        $form
            ->expects($this->atLeastOnce())
            ->method('getRequestArray')
            ->will($this->returnValue($value));

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $form;
    }

}
