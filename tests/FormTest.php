<?php
use TiGR\Form\Element\Checkbox;
use TiGR\Form\Element\Dropdown;
use TiGR\Form\Element\Radio;
use TiGR\Form\Element\Text;
use TiGR\Form\Form;

/**
 * Created by JetBrains PhpStorm.
 * User: tigr
 * Date: 01.04.13
 * Time: 15:00
 * To change this template use File | Settings | File Templates.
 */

class FormTest extends PHPUnit_Framework_TestCase {
    public function testGroupElementsLegacyMode()
    {
        $form = $this->getForm();
        $this->assertEquals('', $form->getActiveGroup());
        $this->assertEquals(array('name', 'email', 'gender', 'color'), array_keys($form->getActiveElements()));
        $form->setActiveGroup('test');
        $this->assertEquals('test', $form->getActiveGroup());
        $this->assertEquals(array('name'), array_keys($form->getActiveElements()));
        $form->includeElementsWithEmptyGroup(true);
        $this->assertEquals(array('name', 'email', 'gender', 'color'), array_keys($form->getActiveElements()));
    }

    public function testGroups()
    {
        $form = $this->getMultiGroupForm();
        $form->setRequestArray(['f' => '1', 'g' => 3]);
        $form->proceed();
        $this->assertEquals(['a', 'b', 'd', 'e', 'f', 'g'], array_keys($form->getActiveElements()));

        $form = $this->getMultiGroupForm();
        $form->setRequestArray(['f' => '1', 'g' => 'z']);
        $form->proceed();
        $this->assertEquals(['a', 'b', 'd', 'f', 'g'], array_keys($form->getActiveElements()));
    }

    public function testStoreState() {
        $form = $this->getForm();
        $_POST['gender'] = 'm';
        $form->setActiveGroup('test');
        $form->proceed();
        $data = $form->exportState();
        $form = $this->getForm();
        $form->importState($data);
        $this->assertEquals('Male', $form->getElement('gender')->getValue());
        $this->assertEquals(['Пожалуйста, заполните поле «Name»'], $form->getErrors());
        $this->assertEquals([], $form->getElement('color')->getValue());
    }

    public function testRequiredValidation() {
        $form = $this->getForm();
        $form->addElement(new Text('test', 'Test', false));
        $form->addElement(new Checkbox('test1', 'Test1', ['test'], false));
        $form->proceed();
        $this->assertEquals([
            'Пожалуйста, заполните поле «Name»',
            'Пожалуйста, заполните поле «Email»',
            'Пожалуйста, заполните поле «Gender»',
            'Пожалуйста, выберите хотя бы одно значение поля «Color»',
        ], $form->getErrors());
    }

    public function getForm() {
        $form = new Form();
        $_POST = ['name' => ''];
        $form->addElement(new Text('name', 'Name', true, 'test'));
        $form->addElement(new Text('email', 'Email', true));
        $form->addElement(new Dropdown('gender', 'Gender', ['m' => 'Male', 'f' => 'Female'], true));
        $form->addElement(new Checkbox('color', 'Color', ['blue' => 'Blue', 'red' => 'Red'], true));
        return $form;
    }

    public function getMultiGroupForm()
    {
        $form = new Form();
        $form->addElement(Text::create('a', '')->setGroups('1'));
        $form->addElement(Text::create('b', '')->setGroups('1 2'));
        $form->addElement(Text::create('c', '')->setGroups('4'));
        $form->addElement(Text::create('d', ''));
        $form->addElement(Text::create('e', '')->setGroups('3'));
        $form->addElement(Radio::create('f', '', ['1' => 1, '2' => 2]));
        $form->addElement(Radio::create('g', '', ['3' => 3, '4' => 4]));
        $form->addGroupControlElement('f');
        $form->addGroupControlElement('g');
        $form->includeElementsWithEmptyGroup(true);

        return $form;
    }
}
