<?php
namespace Kohkimakimoto\Worker\Stats;

use Kohkimakimoto\Worker\Foundation\WorkerStartedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatsEventListener implements EventSubscriberInterface
{
    public function detectedWorkerStarted(WorkerStartedEvent $event)
    {
        $worker = $event->getWorker();

        $worker->stats->setBootTime(new \DateTime());
        if ($worker->stats->isOn()) {
            $worker->eventLoop->addPeriodicTimer($worker->stats->getInterval(), function () use ($worker) {

                $uptime = $worker->stats->getUptime();
                $uptime = gmdate("H:i:s", $uptime);

                $mem = memory_get_usage();
                $memM = round(($mem / 1024 / 1024), 1);
                $worker->output->writeln("<info>Stats report:</info> memory_usage: <comment>$mem</comment> bytes ($memM MB). uptime: <comment>$uptime</comment>. at ".(new \DateTime())->format('Y-m-d H:i:s'));

            });
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'worker.started' => 'detectedWorkerStarted',
        );
    }
}
