<?php


namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class LogController extends AbstractController
{
	/**
	 * Direct logs of Territory Control updates sent by Bossa
	 * 
	 * @Route(path="/bossa_log", methods={"GET"}, name="bossa_log")\
	 * @SWG\Response(response=200, description="Log of all Territory Control actions sent from Bossa")
	 * @SWG\Tag(name="Logs")
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
	 * Logs of all actions taken based on input from bossa_log
	 * 
	 * @Route(path="/tc_updates_log", methods={"GET"}, name="TC Update Log")
	 * @SWG\Response(response=200, description="Log of all actions taken based on TC Updates")
	 * @SWG\Tag(name="Logs")
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
