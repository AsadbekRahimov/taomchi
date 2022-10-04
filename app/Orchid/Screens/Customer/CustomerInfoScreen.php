<?php

namespace App\Orchid\Screens\Customer;

use App\Models\Customer;
use Orchid\Screen\Screen;

class CustomerInfoScreen extends Screen
{
    public $customer;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Customer $customer): iterable
    {
        $this->customer = $customer;
        return [

        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Мижоз: ' . $this->customer->name;
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [];
    }
}
