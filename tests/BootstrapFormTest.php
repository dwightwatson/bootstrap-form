<?php

use Bnb\BootstrapForm\BootstrapForm;

class BootstrapFormTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var BootstrapForm
     */
    protected $bootstrapForm;

    protected $htmlBuilderMock;

    protected $formBuidlerMock;

    protected $configMock;

    protected $sessionMock;


    protected static function callMethod($obj, $name, array $args)
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }


    public function setUp()
    {
        $this->htmlBuilderMock = Mockery::mock('Collective\Html\HtmlBuilder');
        $this->formBuidlerMock = Mockery::mock('Collective\Html\FormBuilder');
        $this->configMock      = Mockery::mock('Illuminate\Contracts\Config\Repository')->shouldDeferMissing();
        $this->sessionMock     = Mockery::mock('Illuminate\Session\SessionManager')->shouldDeferMissing();

        $this->bootstrapForm = new BootstrapForm(
            $this->htmlBuilderMock,
            $this->formBuidlerMock,
            $this->configMock,
            $this->sessionMock
        );
    }


    /** @test */
    public function it_opens_default_form()
    {
        $this->formBuidlerMock->shouldReceive('open')->once()->with([
            'role'  => 'form',
            'class' => 'form-horizontal'
        ])->andReturn('foo');

        $this->configMock->shouldReceive('get')
            ->with('bootstrap_form.type')
            ->once()
            ->andReturn('form-horizontal');

        $result = $this->bootstrapForm->open();

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_opens_store_model_form()
    {
        $model         = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $model->exists = false;

        $this->formBuidlerMock->shouldReceive('model')
            ->once()
            ->with($model, [
                'role'   => 'form',
                'route'  => 'bar',
                'method' => 'POST',
                'class'  => 'form-horizontal',
            ])
            ->andReturn('foo');

        $this->configMock->shouldReceive('get')
            ->with('bootstrap_form.type')
            ->once()
            ->andReturn('form-horizontal');

        $result = $this->bootstrapForm->open([
            'model'  => $model,
            'store'  => 'bar',
            'update' => 'baz'
        ]);

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_opens_update_model_form()
    {
        $model         = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $model->exists = true;

        $model->shouldReceive('getRouteKey')
            ->once()
            ->andReturn(1);

        $this->formBuidlerMock->shouldReceive('model')
            ->once()
            ->with($model, [
                'role'   => 'form',
                'route'  => ['baz', 1],
                'method' => 'PUT',
                'class'  => 'form-horizontal',
            ])
            ->andReturn('foo');

        $this->configMock->shouldReceive('get')
            ->with('bootstrap_form.type')
            ->once()
            ->andReturn('form-horizontal');

        $result = $this->bootstrapForm->open([
            'model'  => $model,
            'store'  => 'bar',
            'update' => 'baz'
        ]);

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_opens_a_vertical_form()
    {
        $this->formBuidlerMock->shouldReceive('open')
            ->with([
                'role'  => 'form',
                'class' => '',
            ])
            ->once()
            ->andReturn('foo');

        $result = $this->bootstrapForm->vertical();

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_opens_an_inline_form()
    {
        $this->formBuidlerMock->shouldReceive('open')
            ->with([
                'class' => 'form-inline',
                'role'  => 'form'
            ])
            ->once()
            ->andReturn('foo');

        $result = $this->bootstrapForm->inline();

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_opens_a_horizontal_form()
    {
        $this->formBuidlerMock->shouldReceive('open')
            ->with([
                'class' => 'form-horizontal',
                'role'  => 'form'
            ])
            ->once()
            ->andReturn('foo');

        $result = $this->bootstrapForm->horizontal();

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_closes_a_form()
    {
        $this->formBuidlerMock->shouldReceive('close')->once()->andReturn('foo');

        $result = $this->bootstrapForm->close();

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_returns_normal_field_names()
    {
        $result = $this->bootstrapForm->flattenFieldName('foo');

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_removes_empty_array_from_field_name()
    {
        $result = $this->bootstrapForm->flattenFieldName('foo[]');

        $this->assertEquals('foo', $result);
    }


    /** @test */
    public function it_flattens_array_from_field_name()
    {
        $result = $this->bootstrapForm->flattenFieldName('foo[bar]');

        $this->assertEquals('foo.bar', $result);
    }


    /** @test */
    public function in_allows_zero_in_field_name()
    {
        $result = $this->bootstrapForm->flattenFieldName('foo[0]');

        $this->assertEquals('foo.0', $result);
    }


    /** @test */
    public function it_flattens_nested_array_from_field_name()
    {
        $result = $this->bootstrapForm->flattenFieldName('foo[bar][baz]');

        $this->assertEquals('foo.bar.baz', $result);
    }


    /** @test */
    public function it_adds_suffix_to_required_fields()
    {
        $this->configMock->shouldReceive('get')
            ->with('bootstrap_form.label_required_mark')
            ->once()
            ->andReturn('*');

        $result = $this->callMethod($this->bootstrapForm, 'getLabelTitle',
            ['Label', 'name', ['required' => true]]);

        $this->assertEquals('Label *', $result);
    }


    /** @test */
    public function it_adds_class_to_required_group_fields()
    {
        $this->formBuidlerMock->shouldReceive('getSessionStore')
            ->once()
            ->andReturn(new \Illuminate\Session\Store('mock',
                new \Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler()));

        $this->configMock->shouldReceive('get')
            ->with('bootstrap_form.group_required_class')
            ->once()
            ->andReturn('required');

        $result = $this->callMethod($this->bootstrapForm, 'getFormGroupOptions',
            ['name', ['required' => true]]);

        $this->assertEquals(['class' => 'form-group required'], $result);
    }


    /** @test */
    public function it_filters_group_options()
    {
        $result = $this->callMethod($this->bootstrapForm, 'getGroupOptions',
            [['required' => true, 'foo' => 'bar']]);

        $this->assertEquals(['required' => true], $result);
    }
}