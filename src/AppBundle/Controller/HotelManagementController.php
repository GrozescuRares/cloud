<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 29.08.2018
 * Time: 10:51
 */

namespace AppBundle\Controller;

use AppBundle\Dto\RoomDto;
use AppBundle\Exception\InappropriateUserRoleException;
use AppBundle\Exception\NoRoleException;
use AppBundle\Form\RoomTypeForm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\OptimisticLockException;

/**
 * Class HotelManagementController
 */
class HotelManagementController extends Controller
{
    /**
     * @Route("/hotel-management/add-room", name="add-room")
     *
     * @param Request $request
     *
     * @return Response
     * @throws OptimisticLockException
     */
    public function addRoomAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelManager = $this->get('app.hotel-management.manager');
        $hotels = $hotelManager->getOwnerHotelsForChoiceType($loggedUser);
        $roomDto = new RoomDto();

        $form = $this->createForm(
            RoomTypeForm::class,
            $roomDto,
            [
                'hotels' => $hotels,
            ]
        );

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render(
                'hotel-management/add-room.html.twig',
                [
                    'add_room_form' => $form->createView(),
                ]
            );
        }

        try {
            $hotelManager->addNewRoom($roomDto);
            $this->addFlash('success', 'The room was successfully added.');

            return $this->redirectToRoute('add-room');
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }
}
