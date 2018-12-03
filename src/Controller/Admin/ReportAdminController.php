<?php

namespace App\Controller\Admin;

use App\Entity\IslandPVEMetal;
use App\Entity\IslandPVPMetal;
use App\Entity\Report;
use App\Entity\ReportMetal;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportAdminController extends CRUDController
{

    /**
     * @param int|string|null $id
     *
     * @return Response
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function approveAction($id = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $id = $request->get($this->admin->getIdParameter());
        /**
         * @var $report Report
         */
        $report = $this->admin->getObject($id);

        if ($report && $report->getIsland()) {
            $island = $report->getIsland();

            if ($report->getMode() === Report::PVE) {
                $islandMetals = $island->getPveMetals();
            } else {
                $islandMetals = $island->getPvpMetals();
            }

            $metalsToAdd = [];
            foreach ($report->getMetals() as $reportMetal) {
                $exists = false;
                foreach ($islandMetals as $islandMetal) {
                    if ($reportMetal->getType() === $islandMetal->getType()) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $metalsToAdd[] = $reportMetal;
                }
            }
            if (!count($metalsToAdd)) {
                $this->addFlash(
                    'sonata_flash_error',
                    'Report not processed, no new metals to add.  Please use override or disapprove this report'
                );


            } else {
                /**
                 * @var $metal ReportMetal
                 */
                foreach ($metalsToAdd as $metal) {
                    if ($report->getMode() === Report::PVE) {
                        $islandMetal = new IslandPVEMetal();
                        $islandMetal->setType($metal->getType());
                        $islandMetal->setQuality($metal->getQuality());
                        $island->addPveMetal($islandMetal);
                    } else {
                        $islandMetal = new IslandPVPMetal();
                        $islandMetal->setType($metal->getType());
                        $islandMetal->setQuality($metal->getQuality());
                        $island->addPvpMetal($islandMetal);
                    }
                }
                $em->persist($island);
                $em->remove($report);
                $em->flush();


                $this->addFlash(
                    'sonata_flash_success',
                    'Report approved, metals added to island without override'
                );
            }
        }

        return $this->redirectToList();
    }

    /**
     * @param int|string|null $id
     *
     * @return Response
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function approveOverrideAction($id = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $id = $request->get($this->admin->getIdParameter());
        /**
         * @var $report Report
         */
        $report = $this->admin->getObject($id);

        if ($report && $report->getIsland()) {
            $island = $report->getIsland();

            if ($report->getMode() === Report::PVE) {
                $islandMetals = $island->getPveMetals();
            } else {
                $islandMetals = $island->getPvpMetals();
            }

            $metalsToAdd = [];
            foreach ($report->getMetals() as $reportMetal) {
                $exists = false;
                foreach ($islandMetals as $islandMetal) {
                    if ($reportMetal->getType() === $islandMetal->getType()) {
                        $islandMetal->setQuality($reportMetal->getQuality());
                        $exists = true;
                    }
                }
                if (!$exists) {
                    if ($report->getMode() === Report::PVE) {
                        $islandMetal = new IslandPVEMetal();
                        $islandMetal->setType($reportMetal->getType());
                        $islandMetal->setQuality($reportMetal->getQuality());
                        $island->addPveMetal($islandMetal);
                    } else {
                        $islandMetal = new IslandPVPMetal();
                        $islandMetal->setType($reportMetal->getType());
                        $islandMetal->setQuality($reportMetal->getQuality());
                        $island->addPvpMetal($islandMetal);
                    }
                }
            }
            $em->persist($island);
            $em->remove($report);
            $em->flush();
            $this->addFlash(
                'sonata_flash_success',
                'Report approved with override, all metals overridden with reported metals and qualities'
            );
        }
        return $this->redirectToList();
    }

    /**
     * @param int|string|null $id
     *
     * @return Response
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function disapproveAction($id = null)
    {

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $id = $request->get($this->admin->getIdParameter());
        $report = $this->admin->getObject($id);

        $em->remove($report);
        $em->flush();


        $this->addFlash(
            'sonata_flash_success',
            'Report disapproved, removed from database'
        );
        return $this->redirectToList();
    }
}
