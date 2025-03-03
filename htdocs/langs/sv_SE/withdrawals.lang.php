<?php
/* Copyright (C) 2012	Regis Houssin	<regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

$withdrawals = array(
		'CHARSET' => 'UTF-8',
		'StandingOrdersArea' => 'Stående order område',
		'CustomersStandingOrdersArea' => 'Kunder stående order område',
		'StandingOrders' => 'Stående order',
		'StandingOrder' => 'Stående order',
		'NewStandingOrder' => 'Nya stående order',
		'StandingOrderToProcess' => 'För att kunna behandla',
		'StandingOrderProcessed' => 'Bearbetade',
		'Withdrawals' => 'Uttag',
		'Withdrawal' => 'Återkallande',
		'WithdrawalsReceipts' => 'Återkallelse kvitton',
		'WithdrawalReceipt' => 'Återkallelse kvitto',
		'WithdrawalReceiptShort' => 'Kvitto',
		'LastWithdrawalReceipts' => 'Senaste %s tillbakadragande kvitton',
		'WithdrawedBills' => 'Indragna fakturor',
		'WithdrawalsLines' => 'Återkallelse linjer',
		'RequestStandingOrderToTreat' => 'Begäran om stående order att behandla',
		'RequestStandingOrderTreated' => 'Begäran om stående behandlas order',
		'CustomersStandingOrders' => 'Kunden stående order',
		'CustomerStandingOrder' => 'Kunden stående order',
		'NbOfInvoiceToWithdraw' => 'Nb av fakturan med dra tillbaka begäran',
		'NbOfInvoiceToWithdrawWithInfo' => 'Nb av faktura med återkalla begäran om kunder som har definierats bankkontoinformation',
		'InvoiceWaitingWithdraw' => 'Faktura väntar på återkalla',
		'AmountToWithdraw' => 'Belopp att dra tillbaka',
		'WithdrawsRefused' => 'Återkallar vägrade',
		'NoInvoiceToWithdraw' => 'Ingen kund faktura betalning läge &quot;tillbaka&quot; väntar. Gå på &quot;Uttag&quot;-fliken på faktura kort att göra en förfrågan.',
		'ResponsibleUser' => 'Ansvarig användare',
		'WithdrawalsSetup' => 'Återkallelse setup',
		'WithdrawStatistics' => 'Dra statistik',
		'WithdrawRejectStatistics' => 'Dra avvisa statistik',
		'LastWithdrawalReceipt' => 'Senaste %s återkalla kvitton',
		'MakeWithdrawRequest' => 'Gör en återkalla begäran',
		'ThirdPartyBankCode' => 'Tredje part bankkod',
		'ThirdPartyDeskCode' => 'Tredje part skrivbord kod',
		'NoInvoiceCouldBeWithdrawed' => 'Ingen faktura withdrawed med framgång. Kontrollera att fakturan på företag med en giltig förbud.',
		'ClassCredited' => 'Klassificera krediteras',
		'ClassCreditedConfirm' => 'Är du säker på att du vill klassificera detta tillbakadragande mottagande som krediteras på ditt bankkonto?',
		'TransData' => 'Datum Transmission',
		'TransMetod' => 'Metod Transmission',
		'Send' => 'Skicka',
		'Lines' => 'Linjer',
		'StandingOrderReject' => 'Utfärda ett förkasta',
		'InvoiceRefused' => 'Ladda avvisande till kund',
		'WithdrawalRefused' => 'Uttag Refuseds',
		'WithdrawalRefusedConfirm' => 'Är du säker på att du vill ange ett tillbakadragande avslag för samhället',
		'RefusedData' => 'Datum för avslag',
		'RefusedReason' => 'Orsak till avslag',
		'RefusedInvoicing' => 'Fakturering avslaget',
		'NoInvoiceRefused' => 'Ladda inte avslaget',
		'InvoiceRefused' => 'Ladda avvisande till kund',
		'Status' => 'Status',
		'StatusUnknown' => 'Okänd',
		'StatusWaiting' => 'Väntar',
		'StatusTrans' => 'Överförs',
		'StatusCredited' => 'Krediteras',
		'StatusRefused' => 'Refused',
		'StatusMotif0' => 'Ospecificerat',
		'StatusMotif1' => 'Bestämmelse insuffisante',
		'StatusMotif2' => 'Tirage conteste',
		'StatusMotif3' => 'Inga uttag för',
		'StatusMotif4' => 'Kundorder',
		'StatusMotif5' => 'RIB inexploitable',
		'StatusMotif6' => 'Konto utan balans',
		'StatusMotif7' => 'Rättsligt beslut',
		'StatusMotif8' => 'Annan orsak',
		'CreateAll' => 'Återta alla',
		'CreateGuichet' => 'Endast kontor',
		'CreateBanque' => 'Endast bank',
		'OrderWaiting' => 'Plats för en ny behandling',
		'NotifyTransmision' => 'Återkallelse Transmission',
		'NotifyEmision' => 'Återkallelse utsläpp',
		'NotifyCredit' => 'Återkallelse Credit',
		'NumeroNationalEmetter' => 'Nationella sändare Antal',
		'PleaseSelectCustomerBankBANToWithdraw' => 'Välj information om kunden bankkonto ta ut',
		'WithBankUsingRIB' => 'För bankkonton med hjälp av RIB',
		'WithBankUsingBANBIC' => 'För bankkonton som använder IBAN / BIC / SWIFT',
		'BankToReceiveWithdraw' => 'Bankkonto för att ta emot drar',
		'CreditDate' => 'Krediter på',
		'WithdrawalFileNotCapable' => 'Det går inte att skapa filen uttag kvitto för ditt land',
		'ShowWithdraw' => 'Visa Dra',
		'IfInvoiceNeedOnWithdrawPaymentWontBeClosed' => 'Om faktura har minst ett uttag betalning som ännu inte behandlats, kommer det inte anges som betalas för att hantera uttag innan.',
		'DoStandingOrdersBeforePayments' => 'Detta flikar gör att du kan begära en stående order. När det kommer att vara färdig, kan du skriva betalningen för att stänga fakturan.',
		////// Notifications
		'InfoCreditSubject' => 'Betalning av %s stående order av banken',
		'InfoCreditMessage' => 'Den stående beställning %s har betalats av banken <br> Uppgifter om betalning: %s',
		'InfoTransSubject' => 'Överföring av %s stående order till bank',
		'InfoTransMessage' => 'Den stående beställning %s har transmited till bank med %s %s. <br><br>',
		'InfoTransData' => 'Belopp: %s <br> Metode: %s <br> Datum: %s',
		'InfoFoot' => 'Detta är ett automatiskt meddelande skickas av Speedealing',
		'InfoRejectSubject' => 'Stående order vägrade',
		'InfoRejectMessage' => 'Hej, <br><br> att ständig ordning faktura %s relaterade till företagets %s med en mängd %s har avslagits av banken. <br><br> - <br> % $',
		'ModeWarning' => 'Alternativ på riktigt läget inte var satt, sluta vi efter denna simulering'
);
?>