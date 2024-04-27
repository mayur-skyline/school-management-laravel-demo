import colors from '../../styles/modules/colors.module.scss';
import AppError from '../errors/AppError';

export function validateAssessmentScore(value: number) {
  if (value < 0 || value > 6 || parseInt(value.toString()) !== value) {
    throw new AppError(`Unsupported assessment score '${value}' provided!`);
  }
  return true;
}

export function getAssessmentScoreColor(value: number) {
  validateAssessmentScore(value);

  if ([1, 6].includes(value)) {
    return 'RED';
  }
  if ([2, 5].includes(value)) {
    return 'AMBER';
  }
  if ([3, 4].includes(value)) {
    return 'GREEN';
  }
}

export function getAssessmentScoreColorClass(
  value: number,
  type: null | 'bg' | 'border' | 'text',
  variant: null | 'alt' | 'transparent' = null,
  selector: 'hover' = null
) {
  validateAssessmentScore(value);

  const color = getAssessmentScoreColor(value)?.toLowerCase();

  return `${selector ? `${selector}:` : ''}${type ? `${type}-` : ''}${color}${
    variant ? `-${variant}` : ''
  }`;
}

export function getAssessmentScoreColorValue(
  value: number,
  variant: null | 'alt' | 'transparent' = null
) {
  validateAssessmentScore(value);

  return colors[getAssessmentScoreColorClass(value, null, variant)];
}
