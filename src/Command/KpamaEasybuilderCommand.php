<?php

namespace Kpama\Easybuilder\Command;

use Illuminate\Console\Command;

class KpamaEasybuilderCommand extends Command 
{

    protected $signature = 'kpama:easybuilder';

    protected $description = 'Kpama Easybuilder dummy command';

    public function handle(){
        $this->info("Package kpama/easybuilder is ready to go");
    }
}