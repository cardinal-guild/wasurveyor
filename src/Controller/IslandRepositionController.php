<?php


namespace App\Controller;


use App\Entity\Island;
use App\Repository\IslandRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class IslandRepositionController extends Controller
{

    /**
     * @return Response
     * @Route("/reposition", name="reposition_islands")
     * @IsGranted("ROLE_SURVEYOR")
     */
    public function repositionAction(Request $request)
    {
        $em =  $this->getDoctrine()->getManager();
        /**
         * @var $islandRepo IslandRepository
         */
        $islandRepo = $em->getRepository('App:Island');

        $adjusted = false;
        if ($request->isMethod('post')) {
            $positions = $request->get('positions');
            if(!empty($positions) && count($positions)) {
                foreach ($positions as $position) {
                    /**
                     * @var Island $island
                     */
                    $island = $islandRepo->find(intval($position['id']));

                    if ($island) {
                        $island->setLat($position['lat']);
                        $island->setLng($position['lng']);
                        $em->persist($island);
                        $adjusted = true;
                    }

                }
                $em->flush();
            }

            if ($adjusted) {
                $this->addFlash("sonata_flash_success", "All positions saved");
            } else {
                $this->addFlash("warning", "No island positions where adjusted");
            }
            return new RedirectResponse($this->generateUrl('reposition_islands'));
        }
        return $this->render('admin/islandreposition.html.twig');
    }
}
