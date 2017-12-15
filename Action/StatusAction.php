<?php

namespace Ekyna\Component\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

/**
 * Class StatusAction
 * @package Ekyna\Component\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $request */

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['vads_trans_id']) {
            $request->markNew();

            return;
        }

        if (false != $code = $model['vads_result']) {
            switch ($code) {
                case "00" : // transaction approuvée ou traitée avec succès
                    $request->markCaptured();
                    break;
                case "02" : // contacter l’émetteur de carte
                    $request->markPending(); // TODO is that the good status ?
                    break;
                case "17" : // Annulation client.
                    $request->markCanceled();
                    break;
                case "03" : // accepteur invalide
                case "04" : // conserver la carte
                case "05" : // ne pas honorer
                case "07" : // conserver la carte, conditions spéciales
                case "08" : // approuver après identification
                case "12" : // transaction invalide
                case "13" : // montant invalide
                case "14" : // numéro de porteur invalide
                case "30" : // erreur de format
                case "31" : // identifiant de l’organisme acquéreur inconnu
                case "33" : // date de validité de la carte dépassée
                case "34" : // suspicion de fraude
                case "41" : // carte perdue
                case "43" : // carte volée
                case "51" : // provision insuffisante ou crédit dépassé
                case "54" : // date de validité de la carte dépassée
                case "56" : // carte absente du fichier
                case "57" : // transaction non permise à ce porteur
                case "58" : // transaction interdite au terminal
                case "59" : // suspicion de fraude
                case "60" : // l’accepteur de carte doit contacter l’acquéreur
                case "61" : // montant de retrait hors limite
                case "63" : // règles de sécurité non respectées
                case "68" : // réponse non parvenue ou reçue trop tard
                case "90" : // arrêt momentané du système
                case "91" : // émetteur de cartes inaccessible
                case "96" : // mauvais fonctionnement du système
                case "94" : // transaction dupliquée
                case "97" : // échéance de la temporisation de surveillance globale
                case "98" : // serveur indisponible routage réseau demandé à nouveau
                case "99" : // incident domaine initiateur
                    $request->markFailed();
                    break;
                default :
                    $request->markUnknown();
            }

            if ($request->isCaptured() && false != $code = $model['state_override']) {
                if ($code == 'refunded') {
                    $request->markRefunded();
                }
            }

            return;
        }

        $request->markNew();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
