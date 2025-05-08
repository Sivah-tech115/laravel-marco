<?php

// app/Console/Commands/GenerateSitemap.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Call the SitemapService to generate the sitemap
        app(SitemapService::class)->generateSitemap();
        
        $this->info('Sitemap generated successfully.');
    }
}

