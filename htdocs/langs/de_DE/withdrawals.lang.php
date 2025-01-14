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
		'StandingOrdersArea' => 'Dauerauftragsübersicht',
		'CustomersStandingOrdersArea' => 'Dauerauftragsübersicht (Kunden)',
		'StandingOrders' => 'Daueraufträge',
		'StandingOrder' => 'Dauerauftrag',
		'NewStandingOrder' => 'Neuer Dauerauftrag',
		'StandingOrderToProcess' => 'Zu bearbeiten',
		'StandingOrderProcessed' => 'Bearbeitet',
		'Withdrawals' => 'Abbuchungen',
		'Withdrawal' => 'Abbuchung',
		'WithdrawalsReceipts' => 'Abbuchungsbelege',
		'WithdrawalReceipt' => 'Abbuchungsbeleg',
		'WithdrawalReceiptShort' => 'Beleg',
		'LastWithdrawalReceipts' => '%s neuste Abbuchungsbelege',
		'WithdrawedBills' => 'Abgebuchte Rechnungen',
		'WithdrawalsLines' => 'Abbuchungszeilen',
		'RequestStandingOrderToTreat' => 'Antrag auf Dauerauftrag zur Behandlung von',
		'RequestStandingOrderTreated' => 'Antrag auf Dauerauftrag behandelt',
		'CustomersStandingOrders' => 'Daueraufträge (Kunden)',
		'CustomerStandingOrder' => 'Dauerauftrag (Kunde)',
		'NbOfInvoiceToWithdraw' => 'Nr. der abzubuchenden Rechnung',
		'NbOfInvoiceToWithdrawWithInfo' => 'Anzahl der Rechnungen mit Abbuchungsanfragen für Kunden mit einem hinterlegten Bankkonto',
		'InvoiceWaitingWithdraw' => 'Rechnung warten auf Abbuchung',
		'AmountToWithdraw' => 'Abbuchungsbetrag',
		'WithdrawsRefused' => 'Abbuchungen abgelehnt',
		'NoInvoiceToWithdraw' => 'Keine Kundenrechnung mit Zahlungsart ""Abbuchung" im Wartezustand. Stellen Sie neue Anträge im \'Abbuchungs\'-Tab der Rechnungskarte.',
		'ResponsibleUser' => 'Verantwortlicher Benutzer',
		'WithdrawalsSetup' => 'Abbuchungseinstellungen',
		'WithdrawStatistics' => 'Abbuchungsstatistik',
		'WithdrawRejectStatistics' => 'Statistik abgelehnter Abbuchungen',
		'LastWithdrawalReceipt' => '%s neueste Abbuchungsbelege',
		'MakeWithdrawRequest' => 'Abbuchungsantrag stellen',
		'ThirdPartyBankCode' => 'BLZ Partner',
		'ThirdPartyDeskCode' => 'Schalter-Code Partner',
		'NoInvoiceCouldBeWithdrawed' => 'Keine Rechnung erfolgreich abgebucht. Überprüfen Sie die Kontonummern der den Rechnungen zugewiesenen Partnern.',
		'ClassCredited' => 'Als eingegangen markieren',
		'ClassCreditedConfirm' => 'Möchten Sie diesen Abbuchungsbeleg wirklich als auf Ihrem Konto eingegangen markieren?',
		'TransData' => 'Überweisungsdatum',
		'TransMetod' => 'Überweisungsart',
		'Send' => 'Senden',
		'Lines' => 'Zeilen',
		'StandingOrderReject' => 'Ablehnung ausstellen',
		'InvoiceRefused' => 'Rechnung abgelehnt',
		'WithdrawalRefused' => 'Abbuchungen abgelehnt',
		'WithdrawalRefusedConfirm' => 'Möchten Sie wirklich eine Abbuchungsablehnung zu diesem Partner erstellen?',
		'RefusedData' => 'Ablehnungsdatum',
		'RefusedReason' => 'Ablehnungsgrund',
		'RefusedInvoicing' => 'Ablehnung in Rechnung stellen',
		'NoInvoiceRefused' => 'Ablehnung nicht in Rechnung stellen',
		'InvoiceRefused' => 'Rechnung abgelehnt',
		'Status' => 'Status',
		'StatusUnknown' => 'Unbekannt',
		'StatusWaiting' => 'Wartend',
		'StatusTrans' => 'Übertragen',
		'StatusCredited' => 'Eingelöst',
		'StatusRefused' => 'Abgelehnt',
		'StatusMotif0' => 'Nicht spezifiziert',
		'StatusMotif1' => 'Unzureichende Deckung',
		'StatusMotif2' => 'Abbuchung angefochten',
		'StatusMotif3' => 'Kein Abbuchungsauftrag',
		'StatusMotif4' => 'Kundenanfrage',
		'StatusMotif5' => 'Fehlerhafte Kontodaten',
		'StatusMotif6' => 'Leeres Konto',
		'StatusMotif7' => 'Gerichtsbescheid',
		'StatusMotif8' => 'Andere Gründe',
		'CreateAll' => 'Alle abbuchen',
		'CreateGuichet' => 'Nur Büro',
		'CreateBanque' => 'Nur Bank',
		'OrderWaiting' => 'Wartend',
		'NotifyTransmision' => 'Abbuchungsüberweisung',
		'NotifyEmision' => 'Abbuchungsemission',
		'NotifyCredit' => 'Abbuchungsgutschrift',
		'NumeroNationalEmetter' => 'Nat. Überweisernummer',
		'PleaseSelectCustomerBankBANToWithdraw' => 'Wählen Sie das Kundenkonto für die Abbuchung',
		'WithBankUsingRIB' => 'Bankkonten mit RIB',
		'WithBankUsingBANBIC' => 'Bankkonten mit IBAN/BIC/SWIFT',
		'BankToReceiveWithdraw' => 'Bankkonto für Abbuchungen',
		'CreditDate' => 'Am',
		'WithdrawalFileNotCapable' => 'Abbuchungsformular für Ihr Land konnte nicht erstellt werden.',
		'ShowWithdraw' => 'Zeige Abbuchung',
		'IfInvoiceNeedOnWithdrawPaymentWontBeClosed' => 'Wenn eine Rechnung mindestens eine noch zu bearbeitende Verbuchung vorweist, kann diese nicht als bezahlt markiert werden.',
		'DoStandingOrdersBeforePayments' => 'Dies erlaubt Ihnen, einen Dauerauftrag anzulegen.',
		////// Notifications
		'InfoCreditSubject' => 'Zahlung des Dauerauftrags %s',
		'InfoCreditMessage' => 'Der Dauerauftrag %s wurde von der Bank gebucht<br>Zahlungsdaten: %s',
		'InfoTransSubject' => 'Übertragung des Dauerauftrags %s',
		'InfoTransMessage' => 'Der Dauerauftrag %s wurde von %s %s übertragen.<br><br>',
		'InfoTransData' => 'Betrag: %s<br>Verwendungszweck: %s<br>Datum: %s',
		'InfoFoot' => 'Dies ist eine automatisierte Nachricht von Speedealing',
		'InfoRejectSubject' => 'Dauerauftrag abgelehnt',
		'InfoRejectMessage' => 'Hallo,<br><br>der Dauerauftrag zur Rechnung %s der Firma %s, über den Betrag von %s wurde abgelehnt.<br><br>--<br>%$',
		'ModeWarning' => 'Echtzeit-Modus wurde nicht aktiviert, wir stoppen nach der Simulation.'
);
?>