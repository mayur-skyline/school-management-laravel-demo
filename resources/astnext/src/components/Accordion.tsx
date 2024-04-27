import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import { Accordion as AccordionBase, AccordionDetails, AccordionSummary } from '@mui/material';
import React from 'react';

type Accordion = React.HTMLAttributes<HTMLElement> & {
  label: React.ReactNode;
  description: string;
};

const Accordion: React.FC<Accordion> = ({ label, description }) => {
  return (
    <AccordionBase
      classes={{
        root: '!shadow-none !border-0 !p-0 before:!h-0',
      }}
    >
      <AccordionSummary
        expandIcon={
          <ExpandMoreIcon className="h-[18px] w-[18px] border rounded-full shadow-[0px_3px_6px_#00000029] text-[7px]" />
        }
        classes={{
          root: '!px-0 !py-[4px] !items-start !min-h-[48px]',
          content: '!m-0',
        }}
      >
        <p className="text-[12px] font-bold">{label}</p>
      </AccordionSummary>
      <AccordionDetails>
        <div className="text-[12px]">{description}</div>
      </AccordionDetails>
    </AccordionBase>
  );
};

export default Accordion;
