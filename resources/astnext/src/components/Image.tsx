import { type NonEmptyString } from '@astnext/@types';
import { getAssetUrl } from '@astnext/common/helpers';
import React from 'react';

type Image = Omit<React.ImgHTMLAttributes<HTMLImageElement>, 'src'> & {
  src: NonEmptyString<string>;
};

const Image: React.FC<Image> = ({ src, ...props }) => {
  const imgSrc = getAssetUrl(src);

  return <img src={imgSrc} alt="" {...props} />;
};

export default Image;
