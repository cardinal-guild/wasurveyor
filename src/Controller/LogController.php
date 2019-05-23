<?php


namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

class LogController extends AbstractController
{
	/**
	 * @Route(path="/bossa_log", name="bossa_log")
	 */
	public function getBossaLogAction() {
		$projectDir = $this->getParameter('kernel.project_dir');
		$environment= $this->getParameter('kernel.environment');
		$finder = new Finder();
		$finder->in($projectDir.'/var/logs')->files()->name('bossa_'.$environment.'.log');
		if($finder->hasResults()) {
			$iterator = $finder->getIterator();
			$iterator->rewind();
			$file = $iterator->current();
			return new Response('<pre>'.$file->getContents().'</pre>');
		}
		return new Response('no log file found');
	}

	/**
	 * @Route(path="/tc_updates_log", name="TC Update Log")
	 */
	public function getTCUpdateLog() {
		$projectDir = $this->getParameter('kernel.project_dir');
		$environment = $this->getParameter('kernel.environment');
		$finder = new Finder();
		$finder->in($projectDir.'/var/logs')->files()->name('tc_updates_'.$environment.'.log');
		if($finder->hasResults()) {
			$iterator = $finder->getIterator();
			$iterator->rewind();
			$file = $iterator->current();
			return new Response('<pre>'.$file->getContents().'</pre>');
		}
		return new Response('no log file found');
	}
}
