<?php

use makeUp\lib\Module;
use makeUp\lib\attributes\Inject;


class Users extends Module
{
    #[Inject('Sampledata')]
    protected $SampleService;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This function prepares the module for rendering.
     *
     * @param string $modName
     * @return string
     */
    protected function build(): string
    {
        $this->SampleService->read();
        $Item = $this->SampleService->getByUniqueId(3);

        $m = [];
        $s = [];

        $m['##MODULE##'] = $Item->getProperty("name");

        return $this->getTemplate()->parse($m, $s);
    }


    /**
     * This is an example of a task
     */
    public function example()
    {
        return;
    }

}