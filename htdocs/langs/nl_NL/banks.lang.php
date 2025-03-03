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

$banks = array(
		'CHARSET' => 'UTF-8',
		'Bank' => 'Bank',
		'Banks' => 'Banken',
		'MenuBankCash' => 'Bank / Kas',
		'MenuSetupBank' => 'Bank / kas instellingen',
		'BankName' => 'Banknaam',
		'FinancialAccount' => 'Rekening',
		'FinancialAccounts' => 'Rekeningen',
		'BankAccount' => 'Bankrekening',
		'BankAccounts' => 'Bankrekeningen',
		'AccountRef' => 'Financiële rekening referentie',
		'AccountLabel' => 'Financiële rekening label',
		'CashAccount' => 'Kasrekening',
		'CashAccounts' => 'Kasrekeningen',
		'MainAccount' => 'Hoofdrekening',
		'CurrentAccount' => 'Betaalrekening',
		'CurrentAccounts' => 'Betaalrekeningen',
		'SavingAccount' => 'Spaarrekening',
		'SavingAccounts' => 'Spaarrekeningen',
		'ErrorBankLabelAlreadyExists' => 'Financiële rekening label bestaat al',
		'BankBalance' => 'Saldo',
		'BalanceMinimalAllowed' => 'Rekeninglimiet',
		'BalanceMinimalDesired' => 'Minimale gewenste saldo',
		'InitialBankBalance' => 'Beginbalans',
		'EndBankBalance' => 'Eindbalans',
		'CurrentBalance' => 'Huidig saldo',
		'FutureBalance' => 'Toekomstig saldo',
		'ShowAllTimeBalance' => 'Toon saldo sinds begin',
		'Reconciliation' => 'Overeenstemming',
		'RIB' => 'Bankrekeningnummer',
		'IBAN' => 'IBAN-nummer',
		'BIC' => 'BIC- / SWIFT-nummer',
		'StandingOrders' => 'Periodieke overboekingen',
		'StandingOrder' => 'Periodieke overboeking',
		'Withdrawals' => 'Opnames',
		'Withdrawal' => 'Opname',
		'AccountStatement' => 'Rekeningafschrift',
		'AccountStatementShort' => 'Afschrift',
		'AccountStatements' => 'Rekeningafschriften',
		'LastAccountStatements' => 'Laatste rekeningafschriften',
		'Rapprochement' => 'Afstemmen',
		'IOMonthlyReporting' => 'Maandelijkse rapportage',
		'BankAccountDomiciliation' => 'Adres rekeninghouder',
		'BankAccountCountry' => 'Land van rekening',
		'BankAccountOwner' => 'Naam rekeninghouder',
		'BankAccountOwnerAddress' => 'Adres rekeninghouder',
		'RIBControlError' => 'Integriteitscontrole mislukt. Dit betekend dat de informatie van deze bankrekening onvolledig of onjuist is (controleer het land, de nummers en de IBAN code).',
		'CreateAccount' => 'Creëer rekening',
		'NewAccount' => 'Nieuw rekening',
		'NewBankAccount' => 'Nieuwe bankrekening',
		'NewFinancialAccount' => 'Nieuwe financiële rekening',
		'MenuNewFinancialAccount' => 'Nieuwe financiële rekening',
		'NewCurrentAccount' => 'Nieuwe betaalrekening',
		'NewSavingAccount' => 'Nieuwe spaarrekening',
		'NewCashAccount' => 'Nieuwe kasrekening',
		'EditFinancialAccount' => 'Wijzig rekening',
		'AccountSetup' => 'Instellingen voor financiële rekeningen',
		'SearchBankMovement' => 'Zoek bankmutaties',
		'Debts' => 'Schulden',
		'LabelBankCashAccount' => 'label van bank of kas',
		'AccountType' => 'Rekeningtype',
		'BankType0' => 'Spaarrekening',
		'BankType1' => 'Betaalrekening',
		'BankType2' => 'Kasrekening',
		'IfBankAccount' => 'Wanneer bankrekening',
		'AccountsArea' => 'Rekeningenoverzicht',
		'AccountCard' => 'Rekeningdetailkaart',
		'DeleteAccount' => 'Rekening verwijderen',
		'ConfirmDeleteAccount' => 'Weet u zeker dat u deze rekening wilt verwijderen?',
		'Account' => 'Rekening',
		'ByCategories' => 'Per categorie',
		'ByRubriques' => 'Per rubriek',
		'BankTransactionByCategories' => 'Bank transacties per categorie',
		'BankTransactionForCategory' => 'Bank transacties voor categorie <b>%s</b>',
		'RemoveFromRubrique' => 'Verwijder link met categorie',
		'RemoveFromRubriqueConfirm' => 'Weet u zeker dat u het verband wilt verwijderen tussen de transactie en de categorie?',
		'ListBankTransactions' => 'Banktransactieslijst',
		'IdTransaction' => 'Transactie ID',
		'BankTransactions' => 'Banktransacties',
		'SearchTransaction' => 'Zoek transactie',
		'ListTransactions' => 'Toon transactielijst',
		'ListTransactionsByCategory' => 'Lijst transactie/categorie',
		'TransactionsToConciliate' => 'Af te stemmen transacties',
		'Conciliable' => 'Kunnen worden afgestemd',
		'Conciliate' => 'Afstemmen',
		'Conciliation' => 'Afstemming',
		'ConciliationForAccount' => 'Deze rekeing afstemmen',
		'IncludeClosedAccount' => 'Inclusief opgeheven rekeningen',
		'OnlyOpenedAccount' => 'Alleen open rekeningen',
		'AccountToCredit' => 'Te crediteren rekening',
		'AccountToDebit' => 'Te debiteren rekening',
		'DisableConciliation' => 'Afstemming van deze rekening uitschakelen',
		'ConciliationDisabled' => 'Afstemming voor deze rekening is uitgeschakeld',
		'StatusAccountOpened' => 'Geopend',
		'StatusAccountClosed' => 'Opgeheven',
		'AccountIdShort' => 'Aantal',
		'EditBankRecord' => 'Bewerk bankregel',
		'LineRecord' => 'Transactie',
		'AddBankRecord' => 'Transactie toevoegen',
		'AddBankRecordLong' => 'Handmatig een transactie toevoegen',
		'ConciliatedBy' => 'Afgestemd door',
		'DateConciliating' => 'Afgestemd op',
		'BankLineConciliated' => 'Transactie afgestemd',
		'CustomerInvoicePayment' => 'Afnemersbetaling',
		'SupplierInvoicePayment' => 'Leveranciersbetaling',
		'WithdrawalPayment' => 'Intrekking betaling',
		'SocialContributionPayment' => 'Sociale bijdrage betaling',
		'FinancialAccountJournal' => 'Dagboek van financiële rekening',
		'BankTransfer' => 'Bankoverboeking',
		'BankTransfers' => 'Bankoverboeking',
		'TransferDesc' => 'Overboeking van de ene rekening naar een andere, Speedealing zal twee boekingen doen (een debitering in de bronrekening en een creditering in de doelrekening, met hetzelfde bedrag. Dezelfde omschrijving en datum zullen worden gebruikt voor deze overboeking)',
		'TransferFrom' => 'Van',
		'TransferTo' => 'Aan',
		'TransferFromToDone' => 'Een overboeking van <b>%s</b> naar <b>%s</b> van <b>%s</b> is geregistreerd.',
		'CheckTransmitter' => 'Overboeker',
		'ValidateCheckReceipt' => 'Deze chequeontvangst valideren?',
		'ConfirmValidateCheckReceipt' => 'Weet u zeker dat u deze chequeontvangst wilt valideren, u kunt dit later niet meer wijzigen ?',
		'DeleteCheckReceipt' => 'Deze chequeontvangst verwijderen?',
		'ConfirmDeleteCheckReceipt' => 'Weet u zeker dat u deze chequeontvangst wilt verwijderen?',
		'BankChecks' => 'Bankcheque',
		'BankChecksToReceipt' => 'Bankcheques te innen',
		'ShowCheckReceipt' => 'Toon controleren stortingsbewijs',
		'NumberOfCheques' => 'Aantal cheques',
		'DeleteTransaction' => 'Verwijderen overboeking',
		'ConfirmDeleteTransaction' => 'Weet u zeker dat u deze overboeking wilt verwijderen ?',
		'ThisWillAlsoDeleteBankRecord' => 'Dit zal ook de gegenereerde bankoverboekingen verwijderen',
		'BankMovements' => 'Mutaties',
		'CashBudget' => 'Waarde kas',
		'PlannedTransactions' => 'Geplande overboekingen',
		'Graph' => 'Grafiek',
		'ExportDataset_banque_1' => 'Bankoverboekingen en rekeningafschriften',
		'TransactionOnTheOtherAccount' => 'Overboeking op de andere rekening',
		'TransactionWithOtherAccount' => 'Rekening overboeking',
		'PaymentNumberUpdateSucceeded' => 'Betalingsnummer succesvol bijgewerkt',
		'PaymentNumberUpdateFailed' => 'Betalingsnummer kon niet worden bijgewerkt',
		'PaymentDateUpdateSucceeded' => 'Betaaldatum succesvol bijgewerkt',
		'PaymentDateUpdateFailed' => 'Betaaldatum kon niet worden bijgewerkt',
		'Transactions' => 'Transacties',
		'BankTransactionLine' => 'Bankoverboeking',
		'AllAccounts' => 'Alle bank-/ kasrekeningen',
		'BackToAccount' => 'Terug naar rekening',
		'ShowAllAccounts' => 'Toon alle rekeningen',
		'FutureTransaction' => 'Overboeking in de toekomst. Geen manier mogelijk om af te stemmen',
		'SelectChequeTransactionAndGenerate' => 'Select / filter controleert op te nemen in het controleren stortingsbewijs en op &quot;Create&quot; klikken.',
		'InputReceiptNumber' => 'Choose the bank statement related with the conciliation. Use a sortable numeric value (such as, YYYYMM)',
		'EventualyAddCategory' => 'Eventually, specify a category in which to classify the records',
		'ToConciliate' => 'To conciliate?',
		'ThenCheckLinesAndConciliate' => 'Then, check the lines present in the bank statement and click'
);
?>