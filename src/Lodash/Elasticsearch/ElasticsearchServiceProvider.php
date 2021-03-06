<?php

declare(strict_types=1);

namespace Longman\LaravelLodash\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(Client::class, static function (Application $app) {
            // Logger instance
            $config = $app['config']->get('services.elastic_search');

            $params = [
                'hosts' => $config['hosts'],
            ];

            if (! empty($config['connectionParams'])) {
                $params['connectionParams'] = $config['connectionParams'];
            }

            $logger = ! empty($config['log_channel']) ? $app['log']->stack($config['log_channel']) : null;
            if ($logger) {
                $params['logger'] = $logger;
            }

            $client = ClientBuilder::fromConfig($params);

            return $client;
        });

        $this->app->singleton(ElasticsearchManagerContract::class, static function (Application $app) {
            $client = $app->make(Client::class);
            $enabled = (bool) $app['config']->get('services.elastic_search.enabled', false);

            return new ElasticsearchManager($client, $enabled);
        });
    }

    public function provides(): array
    {
        return [Client::class, ElasticsearchManagerContract::class];
    }
}
