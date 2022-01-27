<?php
/**
 * Класс реализации обмеда данных с https://sigex.kz/ для подписи документов сертификатами НУЦ
 *
 *
 *
 * Дополнительные данные и ссылки
 *
 * апростой JS клиент для NCALayer.
 *      https://github.com/sigex-kz/ncalayer-js-client
 *
 *
 * https://sigex.kz/show/?id=[id документа]
 * @version 0.1
 * @author Vanuykov M.
 * @copyright 2022
 */


namespace Sigexkz;


/// TODO Переделать массивы в объекты модели
/// TODO Сообщения об ошибках пределать в объет exeption https://sigex.kz/support/developers/#error-messages-overview
/// TODO сделать провекри на пустые поля
///
///
class SigexKzApi extends SigexKzApiAbstract
{

    function __construct()
    {
        parent::__construct();
    }


    /**
     * регистрация нового документа в системе
     * https://sigex.kz/support/developers/#document-registration
     * @param string $title - заголовок документа;
     * @param string $description - описание документа;
     * @param string $signType - опциональное поле, тип регистрируемой подписи, поддерживается два строковых значения "cms" и "xml", по умолчанию "cms";
     * @param string $signature - в случае CMS это должна быть закодированная в base64 подпись, в случае XML - текстовое представление XML;
     * @param array $emailNotifications - опциональный параметр, объект настроек для отправки уведомлений по электронной почте о подписании документа, в данный момент поддерживается только поле to которое должно быть массивом адресов электронной почты;
     * @param array $settings - опциональное поле, объект настроек документа, описание приведено
     *
     * @return object|null
     *      documentId - уникальный идентификатор зарегистрированного документа;
     *      signId - уникальный идентификатор добавленной подписи;
     *      data - опциональное поле, извлеченные из CMS подписанные данные в виде base64 строки, присутствует только в том случае, если переданная в запросе подпись включала подписанные данные.
     */
    public function newDocument($title,
                                $description,
                                $signature,
                                $signType = "cms",
                                $emailNotifications = array(),
                                $settings = array())
    {
        $this->Content_Type = "application/json";
        $defaultSetting = [
            "private" => false,
            "signaturesLimit" => 3,
            "switchToPrivateAfterLimitReached" => false
        ];

        $param = [
            "title" => $title,//- заголовок документа;
            "description" => $description,
            "signType" => $signType,
            "signature" => $signature,
            "emailNotifications" => [
                "to" => $emailNotifications,
            ],
            "settings" => array_merge_recursive($defaultSetting, $settings)
        ];
        $this->Content_Type = "application/json";
        return $this->post('', $param);
    }

    /**
     * фиксация значений хешей документа
     * @return object|null
     */
    public function fixedDocument($documentId, $blobDocument)
    {
        $this->Content_Type = "application/octet-stream";
        return $this->post($documentId . '/data', $blobDocument);
    }


    /**
     * формирование карточки электронного документа
     * https://sigex.kz/support/developers/#build-ddc
     * @return object|null
     */
    public function buildDDC($documentId, $blobDocument, $uploadTofileName = '')
    {
        $this->Content_Type = "application/octet-stream";
        $exp = $this->post($documentId . '/buildDDC', $blobDocument, true);
        if (isset($exp->ddc)) {
            file_put_contents($uploadTofileName, base64_decode($exp->ddc));
            return true;
        }
        return $exp;
    }


    /**
     * получение данных о зарегистрированном документе
     * https://sigex.kz/support/developers/#document-info
     * @param string $documentId идентификатор документа;
     * @param int $lastSignId - опционально, последний идентификатор подписи после которого нужно возвращаться подписи.
     * @return object|null
     */
    public
    function documentInfo($documentId, $lastSignId = 0)
    {
        $this->Content_Type = "application/json";
        return $this->get($documentId, ['lastSignId' => (int)$lastSignId]);
    }

    /**
     * добавление подписи к документу
     * https://sigex.kz/support/developers/#document-add-signature
     * @param string $documentId идентификатор документа;
     * @param string $signature в случае CMS это должна быть закодированная в base64 подпись, в случае XML - текстовое представление XML.
     * @return object|null
     */
    public
    function singAdd($documentId, $signature)
    {
        $this->Content_Type = "application/json";
        $param = [
            "signType" => "cms", //опциональное поле, тип регистрируемой подписи, поддерживается два строковых значения "cms" и "xml", по умолчанию "cms";
            "signature" => $signature
        ];
        return $this->post($documentId, $param);
    }


    /**
     * версия сервиса
     * @return object|null
     * version - версия сервиса;
     * buildTimeStamp - время сборки сервиса в секундах с UNIX Epoch.
     */
    public
    function version()
    {
        return $this->get('version');
    }

    /**
     * Перечень известных строк
     * https://sigex.kz/support/developers/#strings
     * @return object|null
     */
    public
    function strings()
    {
        return $this->get('strings');
    }
}
