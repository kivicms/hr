<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\Enums\NotificationEvent;
use NW\WebService\References\Operations\Notification\Enums\NotificationType;

/**
 * Class TsReturnOperation
 * Код отформатировал по PSR-12
 * Этот класс представляет операцию для обработки возврата товаров в системе.
 * Он расширяет класс ReferencesOperation.
 */
class TsReturnOperation extends ReferencesOperation
{
    /**
     * @throws Exception
     */
    public function doOperation(): array // поменял void на array
    {
        $data = (array)$this->getRequest('data');
        $resellerId = $data['resellerId'];
        $notificationType = NotificationType::from((int)$data['notificationType']);
        $result = [
            'notificationEmployeeByEmail' => false,
            'notificationClientByEmail' => false,
            'notificationClientBySms' => [
                'isSent' => false,
                'message' => '',
            ],
        ];

        if (empty((int)$data['resellerId'])) {
            $result['notificationClientBySms']['message'] = 'Empty resellerId';
            return $result;
        }

        if (empty((int)$data['notificationType'])) {
            throw new Exception('Empty notificationType', 400);
        }

        $reseller = Seller::getById((int)$data['resellerId']);
        if ($reseller === null) {
            throw new Exception('Seller not found!', 400);
        }

        $client = Contractor::getById((int)$data['clientId']);
        if ($client === null || $client->type !== Contractor::TYPE_CUSTOMER || $client->seller->id !== $data['resellerId']) {
            throw new Exception('Client not found!', 400);
        }

        $clientFullName = $client->getFullName();
        if (empty($client->getFullName())) {
            $clientFullName = $client->name;
        }

        $creator = Employee::getById(
            (int)$data['creatorId']
        ); // переменная $cr не несет смысловой нагрузки, переделал на $creator
        if ($creator === null) {
            throw new Exception('Creator not found!', 400);
        }

        $expert = Employee::getById(
            (int)$data['expertId']
        ); // переменная $et не несет смысловой нагрузки, переделал на $expert
        if ($expert === null) {
            throw new Exception('Expert not found!', 400);
        }

        $differences = '';
        if ($notificationType === NotificationType::NEW) {
            $differences = __('NewPositionAdded', null, $resellerId);
        } elseif ($notificationType === NotificationType::CHANGE && !empty($data['differences'])) {
            $differences = __('PositionStatusHasChanged', [
                'FROM' => Status::from((int)$data['differences']['from'])->name(),
                'TO' => Status::from((int)$data['differences']['to'])->name(),
            ], $resellerId);
        }

        $templateData = [
            'COMPLAINT_ID' => (int)$data['complaintId'],
            'COMPLAINT_NUMBER' => (string)$data['complaintNumber'],
            'CREATOR_ID' => (int)$data['creatorId'],
            'CREATOR_NAME' => $creator->getFullName(),
            'EXPERT_ID' => (int)$data['expertId'],
            'EXPERT_NAME' => $expert->getFullName(),
            'CLIENT_ID' => (int)$data['clientId'],
            'CLIENT_NAME' => $clientFullName,
            'CONSUMPTION_ID' => (int)$data['consumptionId'],
            'CONSUMPTION_NUMBER' => (string)$data['consumptionNumber'],
            'AGREEMENT_NUMBER' => (string)$data['agreementNumber'],
            'DATE' => (string)$data['date'],
            'DIFFERENCES' => $differences,
        ];

        // Если хоть одна переменная для шаблона не задана, то не отправляем уведомления
        foreach ($templateData as $key => $tempData) {
            if (empty($tempData)) {
                throw new Exception("Template Data ({$key}) is empty!", 500);
            }
        }

        $emailFrom = getResellerEmailFrom($resellerId);
        // Получаем email сотрудников из настроек
        $emails = getEmailsByPermit($resellerId, 'tsGoodsReturn');
        if (!empty($emailFrom) && count($emails) > 0) {
            foreach ($emails as $email) {
                MessagesClient::sendMessage([
                    [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo' => $email,
                        'subject' => __('complaintEmployeeEmailSubject', $templateData, $resellerId),
                        'message' => __('complaintEmployeeEmailBody', $templateData, $resellerId),
                    ],
                ], $resellerId, NotificationEvent::CHANGE_RETURN_STATUS);
                $result['notificationEmployeeByEmail'] = true;
            }
        }
        $error = '';
        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($notificationType === NotificationType::CHANGE && !empty($data['differences']['to'])) {
            if (!empty($emailFrom) && !empty($client->email)) {
                $error = MessagesClient::sendMessage([
                    [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo' => $client->email,
                        'subject' => __('complaintClientEmailSubject', $templateData, $resellerId),
                        'message' => __('complaintClientEmailBody', $templateData, $resellerId),
                    ],
                ], $resellerId, NotificationEvent::CHANGE_RETURN_STATUS, $client->id, (int)$data['differences']['to']);
                $result['notificationClientByEmail'] = true;
            }

            if (!empty($client->mobile)) {
                $res = NotificationManager::send(
                    $resellerId,
                    $client->id,
                    NotificationEvent::CHANGE_RETURN_STATUS,
                    (int)$data['differences']['to'],
                    $templateData,
                    $error
                );
                if ($res) {
                    $result['notificationClientBySms']['isSent'] = true;
                }
                if (!empty($error)) {
                    $result['notificationClientBySms']['message'] = $error;
                }
            }
        }

        return $result;
    }
}
