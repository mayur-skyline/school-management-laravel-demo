<?php

function biasType($bias_name) {
    $bias_name = strtoupper($bias_name);
    return str_replace(" ", "_", $bias_name);
}

function BiasAbreviationToLabel($bias)
{
    if ($bias == 'dl' || $bias == 'sdl')
        return 'SELF_DISCLOSURE';
    else if ($bias == 'dh' ||  $bias == 'sdh' || $bias == 'sdi')
        return 'SELF_DISCLOSURE';
    else if ($bias == 'tsl')
        return 'TRUST_OF_SELF';
    else if ($bias == 'tsi' || $bias == 'tsh')
        return 'TRUST_OF_SELF';
    else if ($bias == 'tol')
        return 'TRUST_OF_OTHERS';
    else if ($bias == 'toi' || $bias == 'toh')
        return 'TRUST_OF_OTHERS';
    else if ($bias == 'ecl')
        return 'SEEKING_CHANGE';
    else if ($bias == 'eci' || $bias == 'ech')
        return 'SEEKING_CHANGE';
    else if ($bias == 'or' || $bias == 'blu')
        return 'OVER_REGULATION';
    else if ($bias == 'hv')
        return 'HIDDEN_VULNERABILITY';
    else if ($bias == 'sn')
        return 'SOCIAL_NAIVETY';
    else if ($bias == 'sci')
        return 'SEEKING_CHANGE_INSTABILITY';
    else if ($bias == 'ha')
        return 'HIDDEN_AUTONOMY';
    else
        return '';
}

function common_bias($bias)
{
    if ($bias == 'sdh' || $bias == 'sdi')
        return 'dh';
    else if ($bias == 'sdl' )
        return 'dl';
    else if ($bias == 'tsl')
        return 'tsl';
    else if ($bias == 'tsi' || $bias == 'tsh')
        return 'tsh';
    else if ($bias == 'tol')
        return 'tol';
    else if ($bias == 'toi' || $bias == 'toh')
        return 'toh';
    else if ($bias == 'ecl')
        return 'ecl';
    else if ($bias == 'eci' || $bias == 'ech')
        return 'ech';
    else if ($bias == 'or' || $bias == 'blu')
        return 'or';
    else if ($bias == 'hv')
        return 'hv';
    else if ($bias == 'sn')
        return 'sn';
    else if ($bias == 'sci')
        return 'sci';
    else if ($bias == 'ha')
        return 'ha';
    else
        return '';
}

function BiasAbreviationToName($bias)
{
    if ($bias == 'dl' || $bias == 'sdl')
        return 'Polar Low Self Disclosure';
    else if ($bias == 'dh' || $bias == 'sdh' || $bias == 'sdi')
        return 'Polar High Self Disclosure';
    else if ($bias == 'tsl')
        return 'Polar Low Trust Of Self';
    else if ($bias == 'tsi' || $bias == 'tsh')
        return 'Polar High Trust Of Self';
    else if ($bias == 'tol')
        return 'Polar Low Trust Of Others';
    else if ($bias == 'toi' || $bias == 'toh')
        return 'Polar High Trust Of Others';
    else if ($bias == 'eci' || $bias == 'ech')
        return 'Polar High Seeking Change';
    else if ($bias == 'ecl')
        return 'Polar Low Seeking Change';
    else if ($bias == 'or' || $bias == 'blu' )
        return 'Over Regulation';
    else if ($bias == 'hv')
        return 'Hidden Vulnerability';
    else if ($bias == 'sn')
        return 'Social Naivety';
    else if ($bias == 'sci')
        return 'Seeking Change Instability';
    else if ($bias == 'ha')
        return 'Hidden Autonomy';
    else
        return '';
}

function riskTypePolarOrComposite($rawdata) {
    $composite_risk = ['sn','hv','ha','or','sci','blu'];
    $polar_risk = ['dl','sdl', 'dh', 'sdh', 'sdi','tsl','tsh', 'tsi','tol','toh','toi', 'ecl','ech','eci'];
    if(in_array($rawdata->bias, $composite_risk))
        return 'COMPOSITE_RISK';
    if(in_array($rawdata->bias, $polar_risk))
        return 'POLAR_BIAS';
}

function GetBiasLabel($bias) {
    if ($bias == 'dl' || $bias == 'sdl')
            return 'POLAR_LOW_SELF_DISCLOSURE';
        else if ($bias == 'dh' || $bias == 'sdi')
            return 'POLAR_HIGH_SELF_DISCLOSURE';
        else if ($bias == 'tsl')
            return 'POLAR_LOW_TRUST_OF_SELF';
        else if ($bias == 'tsh')
            return 'POLAR_HIGH_TRUST_OF_SELF';
        else if ($bias == 'tol')
            return 'POLAR_LOW_TRUST_OF_OTHERS';
        else if ($bias == 'toh')
            return 'POLAR_HIGH_TRUST_OF_OTHERS';
        else if ($bias == 'ecl')
            return 'POLAR_LOW_SEEKING_CHANGE';
        else if ($bias == 'ech')
            return 'POLAR_HIGH_SEEKING_CHANGE';
        else if ($bias == 'blu' || $bias == 'or')
            return 'OVER_REGULATION';
        else if ($bias == 'hv')
            return 'HIDDEN_VULNERABILITY';
        else if ($bias == 'sn')
            return 'SOCIAL_NAIVETY';
        else if ($bias == 'sci')
            return 'SEEKING_CHANGE_INSTABILITY';
        else if ($bias == 'ha')
            return 'HIDDEN_AUTONOMY';
        else
            return '';
}

function GetBiasInAbbrev($bias)
{
        if ($bias == 'POLAR_LOW_SELF_DISCLOSURE')
            return 'dl';
        else if ($bias == 'POLAR_HIGH_SELF_DISCLOSURE')
            return 'dh';
        else if ($bias == 'POLAR_LOW_TRUST_OF_SELF')
            return 'tsl';
        else if ($bias == 'POLAR_HIGH_TRUST_OF_SELF')
            return 'tsh';
        else if ($bias == 'POLAR_LOW_TRUST_OF_OTHERS')
            return 'tol';
        else if ($bias == 'POLAR_HIGH_TRUST_OF_OTHERS')
            return 'toh';
        else if ($bias == 'POLAR_LOW_SEEKING_CHANGE')
            return 'ecl';
        else if ($bias == 'POLAR_HIGH_SEEKING_CHANGE')
            return 'ech';
        else if ($bias == 'OVER_REGULATION')
            return 'blu';
        else if ($bias == 'HIDDEN_VULNERABILITY')
            return 'hv';
        else if ($bias == 'SOCIAL_NAIVETY')
            return 'sn';
        else if ($bias == 'SEEKING_CHANGE_INSTABILITY')
            return 'sci';
        else if ($bias == 'HIDDEN_AUTONOMY')
            return 'ha';
        else
            return '';
}

function GetBiasInAbbrevAP($bias)
{
    $value = '';
    switch ($bias) {
        case 'POLAR_LOW_SELF_DISCLOSURE':
            $value = 'dl';
            break;
        case 'POLAR_HIGH_SELF_DISCLOSURE':
            $value = 'dh';
            break;
        case 'POLAR_LOW_TRUST_OF_SELF':
            $value = 'tsl';
            break;
        case 'POLAR_HIGH_TRUST_OF_SELF':
            $value = 'tsh';
            break;
        case 'POLAR_LOW_TRUST_OF_OTHERS':
            $value = 'tol';
            break;
        case 'POLAR_HIGH_TRUST_OF_OTHERS':
            $value = 'toh';
            break;
        case 'POLAR_LOW_SEEKING_CHANGE':
            $value = 'ecl';
            break;
        case 'POLAR_HIGH_SEEKING_CHANGE':
            $value = 'ech';
            break;
        case 'OVER_REGULATION':
            $value = 'or';
            break;
        case 'HIDDEN_VULNERABILITY':
            $value = 'hv';
            break;
        case 'HIDDEN_AUTONOMY':
            $value = 'ha';
            break;
        case 'SOCIAL_NAIVETY':
            $value = 'sn';
            break;
        case 'SEEKING_CHANGE_INSTABILITY':
            $value = 'sc';
            break;
        default:
            $value = '';
    }
    return $value;
}

function GetBiasInAbbrev_custom($bias) {
    if ($bias == 'POLAR_LOW_SELF_DISCLOSURE')
        return 'sdl';
    else if ($bias == 'POLAR_HIGH_SELF_DISCLOSURE')
        return 'sdh';
    else if ($bias == 'POLAR_LOW_TRUST_OF_SELF')
        return 'tsl';
    else if ($bias == 'POLAR_HIGH_TRUST_OF_SELF')
        return 'tsh';
    else if ($bias == 'POLAR_LOW_TRUST_OF_OTHERS')
        return 'tol';
    else if ($bias == 'POLAR_HIGH_TRUST_OF_OTHERS')
        return 'toh';
    else if ($bias == 'POLAR_LOW_SEEKING_CHANGE')
        return 'ecl';
    else if ($bias == 'POLAR_HIGH_SEEKING_CHANGE')
        return 'ech';
    else if ($bias == 'OVER_REGULATION')
        return 'or';
    else if ($bias == 'HIDDEN_VULNERABILITY')
        return 'hv';
    else if ($bias == 'SOCIAL_NAIVETY')
        return 'sn';
    else if ($bias == 'SEEKING_CHANGE_INSTABILITY')
        return 'sci';
    else if ($bias == 'HIDDEN_AUTONOMY')
        return 'ha';
    else
        return '';
}

function BiasName($bias) {
    if ($bias == 'SELF_DISCLOSURE') {
        return 'Self Disclosure';
    } else if ($bias == 'TRUST_OF_SELF') {
        return 'Trust Of Self';
    } else if ($bias == 'TRUST_OF_OTHERS') {
        return 'Trust of Others';
    } else if ($bias == 'SEEKING_CHANGE') {
        return 'Seeking Change';
    } else if( $bias == 'OVER_REGULATION') {
        return 'Over Regulation';
    } else if( $bias == 'HIDDEN_VULNERABILITY') {
        return 'Hidden Vulnerability';
    } else if( $bias == 'SEEKING_CHANGE_INSTABILITY') {
        return 'Seeking Change Instability';
    } else if( $bias == 'SOCIAL_NAIVETY') {
        return 'Social Naivety';
    } else if( $bias == 'HIDDEN_AUTONOMY') {
        return 'Hidden Autonomy';
    }
}

function BiasAbreviationToValue($bias, $score)
{
    if ($bias == 'dl')
        return $score['SELF_DISCLOSURE'];
    else if ($bias == 'dh')
        return $score['SELF_DISCLOSURE'];
    else if ($bias == 'tsl')
        return $score['TRUST_OF_SELF'];
    else if ($bias == 'tsi' || $bias == 'tsh')
        return $score['TRUST_OF_SELF'];
    else if ($bias == 'tol')
        return $score['TRUST_OF_OTHERS'];
    else if ($bias == 'toi' || $bias == 'toh')
        return $score['TRUST_OF_OTHERS'];
    else if ($bias == 'ecl')
        return $score['SEEKING_CHANGE'];
    else if ($bias == 'eci' || $bias == 'ech')
        return $score['SEEKING_CHANGE'];
    else if ($bias == 'or')
        return 'OVER_REGULATION';
    else if ($bias == 'hv')
        return 'HIDDEN_VULNERABILITY';
    else if ($bias == 'sn')
        return 'SOCIAL_NAIVETY';
    else if ($bias == 'sci')
        return 'SEEKING_CHANGE_INSTABILITY';
    else if ($bias == 'ha')
        return 'HIDDEN_AUTONOMY';
    else
        return '';
}

function ConvertBias($bias) {
    if($bias  == 'tsi')
        return 'tsh';
    else if($bias == 'toi')
        return 'toh';
    else if($bias == 'eci')
        return 'ech';
    else
        return $bias;
}

function ConvertVariantToLegacyTerm($variant) {
    if($variant == 'IN_SCHOOL')
        return 'con';
    else
        return 'gen';
}

function ConvertRiskToEitherPolarOrComposite($variant, $riskType) {
    if($riskType == 'POLAR_BIAS')
        return 'all';
    else
        return ConvertVariantToLegacyTerm($variant);

}

function GetTypeFromBias($bias, $variant)
{
        if ($bias == 'POLAR_LOW_SELF_DISCLOSURE')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_HIGH_SELF_DISCLOSURE')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_LOW_TRUST_OF_SELF')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_HIGH_TRUST_OF_SELF')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_LOW_TRUST_OF_OTHERS')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_HIGH_TRUST_OF_OTHERS')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_LOW_SEEKING_CHANGE')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'POLAR_HIGH_SEEKING_CHANGE')
            return ConvertRiskToEitherPolarOrComposite($variant, 'POLAR_BIAS');
        else if ($bias == 'OVER_REGULATION')
            return ConvertRiskToEitherPolarOrComposite($variant, 'COMPOSITE');
        else if ($bias == 'HIDDEN_VULNERABILITY')
            return ConvertRiskToEitherPolarOrComposite($variant, 'COMPOSITE');
        else if ($bias == 'SOCIAL_NAIVETY')
            return ConvertRiskToEitherPolarOrComposite($variant, 'COMPOSITE');
        else if ($bias == 'SEEKING_CHANGE_INSTABILITY')
            return ConvertRiskToEitherPolarOrComposite($variant, 'COMPOSITE');
        else if ($bias == 'HIDDEN_AUTONOMY')
            return ConvertRiskToEitherPolarOrComposite($variant, 'COMPOSITE');
        else
            return '';
}

function GetBiasInAbbrevforGroup($bias)
{
        if ($bias == 'POLAR LOW SELF DISCLOSURE')
            return 'sdl';
        else if ($bias == 'POLAR HIGH SELF DISCLOSURE')
            return 'sdi';
        else if ($bias == 'POLAR LOW TRUST OF SELF')
            return 'tsl';
        else if ($bias == 'POLAR HIGH TRUST OF SELF')
            return 'tsi';
        else if ($bias == 'POLAR LOW TRUST OF OTHERS')
            return 'tol';
        else if ($bias == 'POLAR HIGH TRUST OF OTHERS')
            return 'toi';
        else if ($bias == 'POLAR LOW SEEKING CHANGE')
            return 'ecl';
        else if ($bias == 'POLAR HIGH SEEKING CHANGE')
            return 'eci';
        else if ($bias == 'OVER REGULATION')
            return 'blu';
        else if ($bias == 'HIDDEN VULNERABILITY')
            return 'hv';
        else if ($bias == 'SOCIAL Naivety')
            return 'sn';
        else if ($bias == 'SEEKING CHANGE INSTABILITY')
            return 'sci';
        else if ($bias == 'HIDDEN AUTONOMY')
            return 'ha';
        else
            return '';
}

function PlanType($type)
{
    if ($type == '1')
        return 'OUT_OF_SCHOOL';
    else if ($type == '2')
        return 'IN_SCHOOL';
    else
        return '';
}

function RiskName($score, $bias )
{
    if( $bias == 'SELF_DISCLOSURE' )
        $bias = 'Self Disclosure';
    else if ($bias == 'TRUST_OF_SELF')
        $bias = 'Trust of Self';
    else if ($bias == 'TRUST_OF_OTHERS')
        $bias = 'Trust of Others';
    else if ($bias == 'SEEKING_CHANGE')
        $bias = 'Seeking Change';

    if( $score <= 3 )
        return 'Polar low '.$bias.'';
    else if( $score >= 12 )
        return 'Polar high '.$bias.'';
    else
        return null;

}

function PolarPrefix($bias) {
    $low = [ 'sdl', 'dl', 'tsl', 'tol', 'ecl' ];
    $high =  [ 'sdh', 'dh', 'tsh', 'toh', 'ech', 'toi', 'tsi' ];
    if ( in_array( strtolower($bias), $low ) )
        return 'POLAR_LOW_';
    else
        return 'POLAR_HIGH_';
}

function changeStatementToAbbrev( $bias ) {
    if( $bias == 'ha' )
        return 'Hidden Autonomy';
    else if( $bias == 'hv' )
        return 'Hidden Vulnerability';
    else if( $bias == 'sn' )
        return 'Social Naivety';
    else if( $bias == 'sn' )
        return 'Social Naivety';
    else if( $bias == 'or' || $bias == 'blu' )
        return 'Over Regulation';
}

function isCompositeBias( $bias ) {
    if( $bias == 'ha' || $bias == 'hv' || $bias == 'sn' || $bias == 'or' || $bias == 'blu' )
        return true;
    else
        return false;
}

function biasNameShort($bias)
{
    if( strtoupper(trim($bias)) == 'SELF_DISCLOSURE' )
        return 'sd';
    else if (strtoupper(trim($bias)) == 'TRUST_OF_SELF')
        return 'tos';
    else if (strtoupper(trim($bias)) == 'TRUST_OF_OTHERS')
        return 'too';
    else if (strtoupper(trim($bias)) == 'SEEKING_CHANGE')
        return 'sc';
    return null;
}

function biasNameShortV2($bias)
{
    if( strtoupper(trim($bias)) == 'SELF_DISCLOSURE' )
        return 'P';
    else if (strtoupper(trim($bias)) == 'TRUST_OF_SELF')
        return 'S';
    else if (strtoupper(trim($bias)) == 'TRUST_OF_OTHERS')
        return 'L';
    else if (strtoupper(trim($bias)) == 'SEEKING_CHANGE')
        return 'X';
    return null;
}
