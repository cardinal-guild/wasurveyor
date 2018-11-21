<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Repository\IslandRepository;
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
     * Post a PVE report of materials
     *
     * @Route("/pve/report.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Returns all marker data for islands, if query input given, gives islands by search"
     * )
     * @SWG\Tag(name="Reporting")
     * @Cache(public=true, expires="now", mustRevalidate=true)
     */
    public function postPveReportAction(Request $request)
    {

    }

    /**
     * Create a new PVP Report
     *
     * @Route("/pvp/report.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Tag(name="Reporting")
     * @SWG\Response(
     *     response=200,
     *     description="When report is successfull submitted"
     * )
     */
    public function postPvpReportAction()
    {

    }

}
