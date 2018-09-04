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

/**
 * Class HotelManagementController
 * @package AppBundle\Controller
 */
class HotelManagementController extends Controller
{
    /**
     * @Route("/hotel-management/add-room", name="add-room")
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
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

    /**
     * @Route("hotel-management/hotel-information", name="hotel-information")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function hotelInformationAction(Request $request)
    {
        $loggedUser = $this->getUser();
        $hotelManagementManager = $this->get('app.hotel-management.manager');
        $bookingManager = $this->get('app.bookings.manager');

        try {
            $hotelsDto = $hotelManagementManager->getFirstHotels($loggedUser, 0);
            $availableHotels = $bookingManager->getFreeHotels(new \DateTime('now'), new \DateTime('now'));
            $pages = $hotelManagementManager->getHotelPagesNumber($loggedUser);

            return $this->render(
                'hotel-management/hotel-information.html.twig',
                [
                    'user' => $loggedUser,
                    'hotels' => $hotelsDto,
                    'availableHotels' => $availableHotels,
                    'nrPages' => $pages,
                    'currentPage' => 1,
                    'nrHotels' => count($hotelsDto),
                    'filters' => [],
                ]
            );
        } catch (NoRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        } catch (InappropriateUserRoleException $ex) {
            return $this->render('error.html.twig', ['error' => $ex->getMessage()]);
        }
    }
}
