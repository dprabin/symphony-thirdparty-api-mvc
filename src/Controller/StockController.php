<?php

namespace App\Controller;

use App\Contract\FinanceApiClientInterface;
use App\Entity\Stock;
use App\Form\StockType;
use App\Http\YahooFinanceApiClient;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/stocks", name="stocks.")
 */
class StockController extends AbstractController
{
    public function __construct(EntityManagerInterface    $entityManager,
                                FinanceApiClientInterface $financeApiClient,
                                SerializerInterface       $serializer)
    {
        $this->entityManager = $entityManager;

        $this->financeApiClient = $financeApiClient;

        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(StockRepository $stockRepository): Response
    {
        $stocks = $stockRepository->findAll();

        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $stock = new Stock();

        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($stock);
//            $em->flush();

            /** @var JsonResponse $stockProfile */
            $stockProfile = $this->financeApiClient->fetchStockProfile($stock->getSymbol(), 'US');

            /** @var Stock $stock */
            $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');
            $this->entityManager->persist($stock);
            $this->entityManager->flush();

            return $this->redirect($this->generateUrl('stocks.index'));
        }

        return $this->render('stock/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/fetch", name="fetch")
     */
    public function fetch(Request $request, StockRepository $stockRepository)
    {
        /** @var JsonResponse $stockProfile */
        $stockProfile = $this->financeApiClient->fetchStockProfile($request->get('symbol'), 'US');

        if ($stockProfile->getStatusCode() !== 200) {
            $this->addFlash('error', 'Stock profile of ' . $request->get('symbol') . ' couldn\t be updated.');

            return $this->redirect($this->generateUrl('stocks.index'));
        }

        /** @var Stock $stock */
        $stock = $this->serializer->deserialize($stockProfile->getContent(), Stock::class, 'json');
        $stock1 = $stockRepository->findOneBy(['symbol' => $stock->getSymbol()]);

        if ($stock1) {
            $stock1->setPrice($stock->getPrice());
            $stock1->setPreviousClose($stock->getPreviousClose());
            $stock1->setPriceChange($stock->getPriceChange());
            $this->entityManager->persist($stock1);
        } else {
            $this->entityManager->persist($stock);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Stock profile of ' . $stock->getShortName() . ' updated');

        return $this->redirect($this->generateUrl('stocks.index'));
    }
}
