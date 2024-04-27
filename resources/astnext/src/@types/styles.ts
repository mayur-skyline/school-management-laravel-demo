export type CssSize = 'base' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';

export type RAGColor = 'RED' | 'AMBER' | 'GREEN' | 'BLUE' | 'WHITE' | 'GRAYSCALE' | 'BLACK';

export type PresetColor = RAGColor | 'GRAY' | 'AQUA' | 'ORANGE';

export type Sizes =
  | '4'
  | '6'
  | '8'
  | '10'
  | '12'
  | '14'
  | '16'
  | '17'
  | '18'
  | '20'
  | '22'
  | '24'
  | '25'
  | '26'
  | '28'
  | '30'
  | '31'
  | '32'
  | '34'
  | '36'
  | '40'
  | '44'
  | '48'
  | '50'
  | '52'
  | '56'
  | '60'
  | '64'
  | '68'
  | '72'
  | '76'
  | '80'
  | '84'
  | '88'
  | '92'
  | '96'
  | '100'
  | '120'
  | '150'
  | '200'
  | '300'
  | '350'
  | '400'
  | '450'
  | '500';

export type SizeClasses = Record<CssSize, string>;
