<?php


namespace Billing;


enum IssueEnumerator: string
{
    case UNDEFINED_COUNTRY = "Country does not exists.";
    case UNDEFINED = 'undefined';
    case INVALID_UNIT_CHARGE_AMOUNT = "Invalid unit charge amount.";
    case UNIT_CHARGE_DOES_NOT_EXISTS = "Unit charge does not exists.";
    case INVALID_PLMN_NAME = "Invalid PLMN name.";
    case NO_CONSUMPTIONS_ON_PERIOD = "No consumptions found on selected period.";
    case PRODUCT_NOT_FOUND = "Product found is not registered in database.";
    case PRODUCT_PRICES_NOT_FOUND = "Product prices not found.";
    case OVERDATA_IN_BUNDLE_ZONE = "Consumption reached in bundle zone.";
    case DATA_CONSUMPTION_OUT_BUNDLE_ZONE = "Data consumption found out of bundle zone.";
    case POSSIBLE_BUNDLE_ZONE_NAME_MISCONFIGURED = "Possible bundle zone name misconfigured.";
    case PARTIAL_MRC = "Partial MRC";
}