import { type ButtonProps } from '@astnext/components/base/button/Button';
import GreenButton from '@astnext/components/base/button/GreenButton';
import { CircularProgress } from '@mui/material';
import React from 'react';

type ConfirmButton = ButtonProps;

const ConfirmButton: React.FC<ConfirmButton> = ({ loading, onClick, className = '', ...props }) => {
  return (
    <GreenButton
      type="submit"
      disabled={loading}
      className={`!py-[5px] !h-[28px] rounded-[5px] disabled:opacity-80 disabled:cursor-not-allowed flex-shrink-0 ${className}`}
      onClick={onClick}
      {...props}
    >
      <div className="flex items-center justify-center">
        <CircularProgress
          size={14}
          color="inherit"
          className={`mr-2 ${loading ? 'opacity-100' : ' opacity-0 '} transition-colors`}
        />
        <span className="pr-[14px]">Confirm</span>
      </div>
    </GreenButton>
  );
};

export default ConfirmButton;
