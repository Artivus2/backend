<?php

namespace app\commands;

use yii\console\Controller;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\Chain;
use app\models\Chart;
use app\models\Currency;
use app\models\ChartChain;
use app\models\PaymentType;
use app\models\Faq;
use app\components\CoinPaymentsAPI;


class HelperController extends BaseController
{

    public function actionFaq()
    {
        $json = json_decode('{"status":"OK","type":"GENERAL","code":"000000000","errorData":null,"data":{"entryTreeList":[{"id":null,"question":"Торговля","tag":null,"disabled":false,"childes":[{"id":null,"question":"Спот","tag":null,"disabled":false,"childes":[{"id":null,"question":"Маркет/Лимит/Стоп-Лимит/ОСО ордер","tag":"spot_trading_spot_order_types","disabled":false,"childes":null},{"id":null,"question":"Как торговать криптовалютой","tag":"spot_trading_how_to_trade_spot","disabled":false,"childes":null},{"id":null,"question":"История спотовых ордеров","tag":"spot_trading_spot_trading_history","disabled":false,"childes":null},{"id":null,"question":"Не удается разместить спотовый ордер","tag":"spot_trading_unable_to_place_spot_order","disabled":false,"childes":null},{"id":null,"question":"Как оплачивать комиссию в BNB","tag":"account_function_using_bnb_to_pay_for_fees","disabled":false,"childes":null},{"id":null,"question":"Настройка индикаторов графика","tag":"spot_trading_chart_related_questions","disabled":false,"childes":null},{"id":null,"question":"Оставить комментарий относительно токена","tag":"app_web_client_feedback_on_listen_token","disabled":false,"childes":null}]},{"id":null,"question":"Фьючерс","tag":null,"disabled":false,"childes":[{"id":null,"question":"Фьючерсная торговля: ограничения для пользователей из России","tag":"futures_trading_Ru_users_sanctions","disabled":false,"childes":null},{"id":null,"question":"История фьючерсных ордеров","tag":"futures_trading_futures_history","disabled":false,"childes":null},{"id":null,"question":"Как рассчитать цену ликвидации фьючерсов","tag":"futures_trading_futures_liquidation_price","disabled":false,"childes":null},{"id":null,"question":"Неверный баланс на фьючерсном аккаунте","tag":"futures_trading_futures_wallet_balance_issues","disabled":false,"childes":null},{"id":null,"question":"Не удается отменить фьючерсный ордер","tag":"futures_trading_unable_to_cancel_futures_order","disabled":false,"childes":null},{"id":null,"question":"Правила фьючерсной торговли","tag":"futures_trading_futures_trading_rules","disabled":false,"childes":null}]},{"id":null,"question":"Маржинальная","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как торговать на марже","tag":"margin_trading_how_to_do_margin_trading","disabled":false,"childes":null},{"id":null,"question":"История маржинальных ордеров","tag":"margin_trading_margin_trading_history","disabled":false,"childes":null},{"id":null,"question":"История ликвидаций маржинального аккаунта","tag":"margin_trading_margin_liquidation_price","disabled":false,"childes":null},{"id":null,"question":"Как занимать и погашать долги ","tag":"margin_trading_how_to_borrow_repay","disabled":false,"childes":null},{"id":null,"question":"Не удается занять средства в маржинальном аккаунте","tag":"margin_trading_borrow_exceptions","disabled":false,"childes":null}]},{"id":null,"question":"Токены с кредитным плечом","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как начать торговать токенами с кредитным плечом","tag":"leveraged_tokens_how_to_get_start_with_blt","disabled":false,"childes":null}]},{"id":null,"question":"Опционы(options trading)","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как торговать опционами Binance","tag":"options_trading_how_to_trade_options","disabled":false,"childes":null}]}]},{"id":null,"question":"Email/SMS/Google/Yubikey","tag":null,"disabled":false,"childes":[{"id":null,"question":"Почта/Телефон(смс)/Гугл 2FA/Yubikey","tag":null,"disabled":false,"childes":[{"id":null,"question":"Сбросить/отключить 2FA","tag":"2fa_function_how_to_reset_2fa","disabled":false,"childes":null},{"id":null,"question":"Как изменить адрес электронной почты аккаунта","tag":"account_function_how_to_change_email","disabled":false,"childes":null},{"id":null,"question":"Как подключить Google 2FA","tag":"2fa_function_register_with_phone_number","disabled":false,"childes":null},{"id":null,"question":"Не приходит СМС код","tag":"2fa_function_unable_to_receive_sms_code","disabled":false,"childes":null},{"id":null,"question":"Ошибка кода Google 2FA","tag":"2fa_function_google_2fa_code_error","disabled":false,"childes":null},{"id":null,"question":"Вопросы по Yubikey","tag":"2fa_function_yubikey_issues","disabled":false,"childes":null},{"id":null,"question":"Как активировать биометрическую аутентификацию","tag":"2fa_function_how_to_enable_biometric_verification","disabled":false,"childes":null}]}]},{"id":null,"question":"Безопасность ","tag":null,"disabled":false,"childes":[{"id":null,"question":"Безопасность ","tag":null,"disabled":false,"childes":[{"id":null,"question":"Сообщить о мошенничестве (не связано с P2P)","tag":"security_issue_self_report_scammer","disabled":false,"childes":null},{"id":null,"question":"Советы по безопасности аккаунта","tag":"security_issue_account_security_tips","disabled":false,"childes":null},{"id":null,"question":"Что такое Пылевая Атака","tag":"security_issue_dusting_attack","disabled":false,"childes":null}]}]},{"id":null,"question":"Финансы и Промоакции","tag":null,"disabled":false,"childes":[{"id":null,"question":"Промоакции / Центр наград","tag":null,"disabled":false,"childes":[{"id":null,"question":"Я выполнил все задания промоакции, но не получил вознаграждение","tag":"website_activities_promotions_how_to_check_reward_center","disabled":false,"childes":null},{"id":null,"question":"Новогодний конкурс для комьюнити","tag":"website_activities_promotions_RU","disabled":false,"childes":null},{"id":null,"question":"Binance Football Fever 2022","tag":"fan_token_football_fever_2022","disabled":false,"childes":null},{"id":null,"question":"Учитесь и зарабатывайте с Binance (Learn and Earn)","tag":"website_activities_promotions_learn_and_earn","disabled":false,"childes":null},{"id":null,"question":"Промоакция Crypto WODL","tag":"website_activities_promotions_crypto_wodl","disabled":false,"childes":null},{"id":null,"question":"Промоакция: Binance Fiat Challenge","tag":"website_activities_promotions_fiat_deposit_promo","disabled":false,"childes":null},{"id":null,"question":"Реферальные промо активности","tag":"website_activities_promotions_referral_promotion","disabled":false,"childes":null},{"id":null,"question":"Binance Live активности","tag":"website_activities_promotions_binance_live_activity","disabled":false,"childes":null},{"id":null,"question":"Binance Pay раздача Crypto Box","tag":"binance_pay_crypto_box_giveaway","disabled":false,"childes":null},{"id":null,"question":"Промо: Отправьте 0,01 BUSD/USDT и получите вознаграждение","tag":"binance_pay_c2c_campaign","disabled":false,"childes":null},{"id":null,"question":"Игра Binance Pay 1 BUSD","tag":"binance_pay_1_busd_campaign","disabled":false,"childes":null},{"id":null,"question":"Сообщество Binance / официальные соц сети","tag":"website_activities_promotions_community","disabled":false,"childes":null},{"id":null,"question":"Промо: Подарочная карта Binance","tag":"binance_gift_card_campaign","disabled":false,"childes":null}]},{"id":null,"question":"Binance Simple Earn","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как подписаться на продукты Binance Simple Earn","tag":"savings_how_to_subscribe_savings_product","disabled":false,"childes":null},{"id":null,"question":"Как принять участие в ETH 2.0 Стейкинге","tag":"staking_how_to_participate_eth_2_staking","disabled":false,"childes":null},{"id":null,"question":"Как подписаться на Фиксированные продукты в Binance Simple Earn","tag":"staking_staking_rules","disabled":false,"childes":null},{"id":null,"question":"Как отменить подписку на Гибкие продукты Simple Earn","tag":"savings_how_to_redeem_savings_product","disabled":false,"childes":null},{"id":null,"question":"Как получить тестовый депозитный фонд","tag":"savings_how_to_redeem_savings_trial_fund","disabled":false,"childes":null}]},{"id":null,"question":"Binance  майнинг пул ","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как проверить ежедневную прибыль от майнинг пул","tag":"mining_pool_how_to_check_daily_profit","disabled":false,"childes":null},{"id":null,"question":"Как использовать Binance майнинг пул","tag":"mining_pool_how_to_use_mining_pool","disabled":false,"childes":null},{"id":null,"question":"Как пригласить друга в майнинг пул","tag":"mining_pool_how_to_refer_in_mining_pool","disabled":false,"childes":null}]},{"id":null,"question":"Криптозайм","tag":null,"disabled":false,"childes":[{"id":null,"question":"Как использовать криптозаймы","tag":"loan_how_to_use_loans","disabled":false,"childes":null}]}]},{"id":null,"question":"NFT","tag":null,"disabled":false,"childes":[{"id":null,"question":"NFT","tag":null,"disabled":false,"childes":[{"id":null,"question":"Криштиану Роналду запускает первую коллекцию NFT на Binance","tag":"nft_issue_cr7_nft_collection","disabled":false,"childes":null},{"id":null,"question":"Как создать NFT","tag":"nft_issue_how_to_create_an_nft","disabled":false,"childes":null},{"id":null,"question":"Как купить NFT","tag":"nft_issue_buy_nft","disabled":false,"childes":null},{"id":null,"question":"Как продать NFT","tag":"ntf_issue_sell_nft","disabled":false,"childes":null},{"id":null,"question":"Время аудита NFT","tag":"nft_issue_audit_time","disabled":false,"childes":null},{"id":null,"question":"Аномальные транзакция NFT","tag":"nft_issue_abnormal_transaction","disabled":false,"childes":null},{"id":null,"question":"Комиссии за торговую площадку Binance NFT","tag":"nft_issue_fees_for_nft_marketplace","disabled":false,"childes":null}]}]},{"id":null,"question":"Мобильное/веб приложение","tag":null,"disabled":false,"childes":[{"id":null,"question":"Мобильное приложение/Веб приложение/Клиент","tag":null,"disabled":false,"childes":[{"id":null,"question":"Отправить предложение","tag":"app_web_client_suggestion","disabled":false,"childes":null},{"id":null,"question":"Настройки приложения","tag":"app_web_client_app_settings","disabled":false,"childes":null},{"id":null,"question":"Проблемы с приложением/клиентом","tag":"app_web_client_app_malfunction","disabled":false,"childes":null},{"id":null,"question":"Как загрузить приложение для Windows & Mac","tag":"app_web_client_how_to_download_client","disabled":false,"childes":null}]}]}],"hotQuestion":[{"id":null,"question":"Ограничение 10 000 Евро для пользователей из России","tag":"account_function_Russian_users","intent":null,"childes":null},{"id":null,"question":"Binance Card для беженцев из Украины","tag":"binance_card_refugee_program","intent":null,"childes":null},{"id":null,"question":"Как верифицировать личный аккаунт","tag":"account_function_verify_personal_account","intent":null,"childes":null},{"id":null,"question":"P2P аккаунт отключен/невозможно торговать на P2P","tag":"p2p_trade_unable_to_trade_with_p2p","intent":null,"childes":null},{"id":null,"question":"Как изменить адрес проживания для верификации","tag":"account_function_account_verification_info","intent":null,"childes":null},{"id":null,"question":"Номер телефона/адрес элпочты уже зарегистрирован в системе","tag":"account_function_email_cellphone_is_occupied","intent":null,"childes":null},{"id":null,"question":"Не приходит код по СМС","tag":"2fa_function_unable_to_receive_sms_code","intent":null,"childes":null},{"id":null,"question":"Депозит без тега/ Неверный тег","tag":"crypto_deposit_deposit_without_tag_memo","intent":null,"childes":null},{"id":null,"question":"Как изменить имя","tag":"account_function_user_name_correction","intent":null,"childes":null},{"id":null,"question":"Депозит подтверждён в блокчейне, но не зачислен на счёт","tag":"crypto_deposit_deposit_not_credited","intent":null,"childes":null},{"id":null,"question":"Как зарегистрироваться на Binance","tag":"account_function_how_to_register","intent":null,"childes":null},{"id":null,"question":"Не приходит сообщение по электронной почте","tag":"account_function_unable_to_receive_email","intent":null,"childes":null}],"hasNext":true},"subData":null,"params":null}', true);

        foreach ($json["data"]["entryTreeList"] as $item) {
            $main = new Faq(["parent_id" => 0, "title" => $item["question"]]);
            //$main->save();
            foreach ($item["childes"] as $child) {
                $next = new Faq(["parent_id" => $main->id, "title" => $child["question"]]);
                //$next->save();
                foreach ($child["childes"] as $child2) {
                    $last = new Faq(["parent_id" => $next->id, "title" => $child2["question"], "description" => "add"]);
                    //$last->save();
                }
            }
        }
    }


    public function actionRates()
    {
        $cps_api = new CoinpaymentsAPI();
        $cps_api->Setup('76479a5aF47AAaEf758Cb1297880FB59Cb724f62012c3E1b1f7685cF3Ab4Db91', 'fdc2cb0894961d95b7bace09cdd7aeab28171ba4d961a3b54c4ff23fa6ecdf9e');
        $data = $cps_api->GetRates(false);

        foreach ($data["result"] as $key => $item) {
            $symbol = explode(".", $key)[0];

            print_r($item["name"]);

            $chart = Chart::findOne(["symbol" => $symbol]);
            if(!$chart) {
                $chart = new Chart(["symbol" => $symbol]);
                $chart->active = 1;
                $chart->name = $item["name"];
                $chart->yield = 50;
                $chart->priority = 0;
                $chart->save();

                $chain_name = isset($item["chain"]) ? $item["chain"] : $symbol;
                $chain = Chain::findOne(["name" => $chain_name]);
                if(!$chain) {
                    $chain = new Chain(["name" => $chain_name]);
                    $chain->save();
                }
                $chartChain = new ChartChain(["symbol" => $key, "chain_id" => $chain->id, "chart_id" => $chart->id]);
                $chartChain->save();
            }

        }
    }

    public function actionBank()
    {
        $currencys = Currency::find()->where(["active" => 1])->all();
        foreach ($currencys as $currency) {
            $params = array(
                'fiat' => $currency->symbol
            );

            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'content' => json_encode($params),
                    'header' => "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
                )
            );

            $context = stream_context_create($options);
            $result = file_get_contents("https://p2p.binance.com/bapi/c2c/v2/public/c2c/adv/filter-conditions", false, $context);
            $json_data = json_decode($result, true);

            foreach ($json_data["data"]["tradeMethods"] as $item) {
                $payment = PaymentType::findOne(["currency_id" => $currency->id, 'name' => $item["tradeMethodName"]]);
                if(!$payment) {
                    $payment = new PaymentType(["currency_id" => $currency->id, 'name' => $item["tradeMethodName"]]);
                    $payment->save();
                }
            }
        }
    }

    public function actionChart()
    {
        $chart_query = Chart::find()->where(["active" => 1])->andWhere(["<>", "symbol", "USDT"])->all();

        foreach ($chart_query as $chart) {
            $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode([$chart->symbol . 'USDT']));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $res = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($res, true);
            if(isset($result["code"])) {
                echo $chart->symbol . " - 1\n";
                $chart->active = 0;
                $chart->save();
            } else {
                echo $chart->symbol . " - 0\n";
            }

        }
    }

    public function actionClear()
    {
        $chart_query = Chart::find()->where(["active" => 0])->all();
        foreach ($chart_query as $chart) {
            $chain_query = ChartChain::find()->where(["chart_id" => $chart->id])->all();
            foreach ($chain_query as $chain_item) {
                $count = ChartChain::find()->where(["chain_id" => $chain_item->chain_id])->count();
                if($count == 1) {
                    Chain::deleteAll(['id' => $chain_item->chain_id]);
                }
                $chain_item->delete();
            }
            $chart->delete();
        }
    }

    public function actionCurrency()
    {
        $json = file_get_contents("https://p2p.binance.com/bapi/fiat/v1/public/fiatpayment/menu/currency");
        $json_data = json_decode($json, true);

        foreach ($json_data["data"]["currencyList"] as $item) {

            $currency = Currency::findOne(["symbol" => $item["name"]]);
            if(!$currency) {
                $currency = new Currency(["symbol" => $item["name"], 'name' => $item["name"]]);
                $currency->save();
            }
        }
    }

}