# NNTP Component

Network News Transfer Protocol (NNTP) bindings for React.
This component builds on top of the `Socket` component to implement NNTP.

[![Build Status](https://travis-ci.org/RobinvdVleuten/reactphp-nntp.png?branch=master)](https://travis-ci.org/RobinvdVleuten/reactphp-nntp)

## Install

The recommended way to install reactphp-nntp is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "rvdv/react-nntp": "1.0.*@dev"
    }
}
```

## Basic Usage

Here is a simple example that fetches the first 100 articles from the 'php.doc' newsgroup
of the PHP mailing list.

```php
$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$client = React\Nntp\Client::factory($loop, $dns);

$group = null;
$format = null;

$client
    ->connect('news.php.net', 119)
    ->then(function ($response) use ($client) {
        $command = new React\Nntp\Command\OverviewFormatCommand();
        return $client->sendCommand($command);
    })
    ->then(function (React\Nntp\Command\OverviewFormatCommand $command) use (&$format, $client) {
        $format = $command->getFormat();

        $command = new React\Nntp\Command\GroupCommand('php.doc');
        return $client->sendCommand($command);
    })
    ->then(function (React\Nntp\Command\GroupCommand $command) use (&$group, &$format, $client) {
        $group = $command->getGroup();

        $command = new React\Nntp\Command\OverviewCommand($group->getFirst() . '-' . ($group->getFirst() + 99), $format);
        return $client->sendCommand($command);
    })
    ->then(function (React\Nntp\Command\OverviewCommand $command) use ($client) {
        $articles = $command->getArticles();
        // Process the articles further.

        $client->loop->stop();
    });

$client->loop->run();
```

## Tests

To run the test suite, you need PHPUnit.

    $ phpunit
