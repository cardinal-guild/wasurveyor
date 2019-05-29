<?php


namespace App\Controller;


use App\Entity\Character;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api/account")
 */
class AccountApiController extends FOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Validate an account with api tokens
     *
     * @Route("/validate.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Validate an account with api tokens"
     * )
     * @SWG\Tag(name="Account")
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @View()
     */
    public function validateApiKey(Request $request)
    {

        return true;
    }

    /**
     * Logout current authenticated user
     *
     * @Route("/logout.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Logout current authenticated user"
     * )
     * @SWG\Tag(name="Account")
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @View()
     */
    public function logout(Request $request)
    {
        $user = $this->getUser();
        if($request->query->has('all')) {
            if ($user) {
                $user->setApiToken(Uuid::uuid4()->toString());
                $this->em->persist($user);
                $this->em->flush();
            }
        }
        return true;
    }

    /**
     * Create a new character
     *
     * @Route("/character/create.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Validate an account with api tokens"
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     type="string",
     *     required=true
     * )
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @SWG\Tag(name="Account")
     * @View()
     */
    public function createCharacter(Request $request)
    {
        $user = $this->getUser();
        if(!$request->request->get('name')) {
            throw new BadRequestHttpException("No character name given");
        }

        /**
         * @var $characterRepo CharacterRepository
         */
        $characterRepo = $this->getDoctrine()->getRepository('App:Character');
        $characterCount = $characterRepo->getCharacterCountByOwner($user);
        if($characterCount >= 10) {
            throw new BadRequestHttpException("You cannot have more then 10 characters per account");
        }
        $character = new Character();
        $character->setOwner($user);
        $character->setName($request->request->get('name'));
        $this->em->persist($character);
        $this->em->flush();
        return $character->getGuid();
    }

    /**
     * Delete an existing new character
     *
     * @Route("/character/delete/{guid}.{_format}", methods={"DELETE"}, defaults={ "_format": "json" }, requirements={"guid"="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}" })
     * @SWG\Response(
     *     response=200,
     *     description="Delete an existing new character"
     * )
     * @SWG\Parameter(
     *     name="guid",
     *     in="query",
     *     type="string",
     *     required=true
     * )
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @SWG\Tag(name="Account")
     * @View()
     */
    public function deleteCharacter(Request $request, string $guid)
    {
        $user = $this->getUser();
        if(!$guid) {
            throw new BadRequestHttpException("No character guid given");
        }
        $character = $this->getDoctrine()->getRepository('App:Character')->findOneBy(['guid'=>$guid,'owner'=>$user->getId()]);
        if($character) {
            $this->em->remove($character);
            $this->em->flush();
            return true;
        }
        return false;
    }

    /**
     * Get all your characters
     *
     * @Route("/characters.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Get all your characters"
     * )
     * @SWG\Tag(name="Account")
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @View()
     */
    public function getCharacters(Request $request)
    {
        $user = $this->getUser();

        /**
         * @var $characterRepo CharacterRepository
         */
        $characterRepo = $this->getDoctrine()->getRepository('App:Character');
        $characters = $characterRepo->getAllCharactersForOwner($user);
        return $characters;
    }

    /**
     * Set an island as visited
     *
     * @Route("/character/visit.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Set an island as visited"
     * )
     * @SWG\Parameter(
     *     name="guid",
     *     in="query",
     *     type="string",
     *     description="Character GUID",
     *     required=true
     * )
     *  @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="string",
     *     description="Island ID",
     *     required=true
     * )
     * @SWG\Tag(name="Account")
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @View()
     */
    public function addVisitedIsland(Request $request)
    {
        $user = $this->getUser();
        $guid = $request->request->get('guid');
        $islandId = $request->request->get('id');
        if(!$guid) {
            throw new BadRequestHttpException("No character guid given");
        }
        if(!$islandId) {
            throw new BadRequestHttpException("No island id given");
        }
        /**
         * @var $character Character
         */
        $character = $this->getDoctrine()->getRepository('App:Character')->findOneBy(['guid'=>$guid,'owner'=>$user->getId()]);
        if(!$character) {
            throw new BadRequestHttpException("Character not found");
        }
        if($character) {
            $character->addVisitedIsland($islandId);
            $this->em->persist($character);
            $this->em->flush();
            return true;
        }
        return false;
    }

    /**
     * Set an island as unvisited
     *
     * @Route("/character/unvisit.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Set an island as unvisited"
     * )
     * @SWG\Parameter(
     *     name="guid",
     *     in="query",
     *     type="string",
     *     description="Character GUID",
     *     required=true
     * )
     *  @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="string",
     *     description="Island ID",
     *     required=true
     * )
     * @SWG\Tag(name="Account")
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="UUID", description="UUID Authorization key" )
     * @View()
     */
    public function removeVisitedIsland(Request $request)
    {
        $user = $this->getUser();
        $guid = $request->request->get('guid');
        $islandId = $request->request->get('id');
        if(!$guid) {
            throw new BadRequestHttpException("No character guid given");
        }
        if(!$islandId) {
            throw new BadRequestHttpException("No island id given");
        }
        /**
         * @var $character Character
         */
        $character = $this->getDoctrine()->getRepository('App:Character')->findOneBy(['guid'=>$guid,'owner'=>$user->getId()]);
        if(!$character) {
            throw new BadRequestHttpException("Character not found");
        }
        if($character) {
            $character->removeVisitedIsland($islandId);
            $this->em->persist($character);
            $this->em->flush();
            return true;
        }
        return false;
    }


}
