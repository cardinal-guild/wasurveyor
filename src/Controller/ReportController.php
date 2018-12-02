<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Entity\Report;
use App\Form\Type\ReportType;
use App\Repository\IslandRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use function MongoDB\BSON\toJSON;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api")
 */
class ReportController extends FOSRestController
{
    /**
     * Post a report of materials of an island
     *
     * @Route("/report.{_format}", methods={"OPTIONS","POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Reporting is send correctly"
     * )*
     * @SWG\Response(
     *     response=400,
     *     description="Reporting is not accepted"
     * )
     * @SWG\Tag(name="Reporting")
     * @Cache(public=true, expires="now", mustRevalidate=true)
     */
    public function postReportAction(Request $request, EntityManagerInterface $entityManager)
    {

        /**
         * @var ReportRepository $reportRepo
         */
        $reportRepo = $entityManager->getRepository(Report::class);

        $data = json_decode($request->getContent(), true);
        $data['ipAddress'] = $request->getClientIp();

        if($reportRepo->hasSpamReported((integer)$data['island'], $data['ipAddress'])) {
            return new JsonResponse(['message'=>'Too much reports for this island from this ip, within last 8 hours'], 400);
        }

        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);
        $form->submit($data);
        if ($form->isSubmitted()) {
//            if (!$form->isValid()) {
//                return new JsonResponse(['message'=>'Report is not valid'], 400);
//            }
            $report = $form->getNormData();
//            $entityManager->persist($report);
//            $entityManager->flush();
            return new JsonResponse(['message'=>'Report successfull received'], 200);
        }

    }

}
