import { Radio } from '@mui/material';
import { FC } from 'react';

import { TableCell } from '../Permissions';

const PermissionRadioRow: FC<{
  title?: string;
  items: boolean[];
  onClick?: (rId: number, cId: number) => void;
  rId: number;
}> = (props) => {
  return (
    <>
      {props.items.map((ckeck, index) => {
        return (
          <TableCell key={index} align="center">
            <Radio
              checked={ckeck}
              onChange={props.onClick.bind(null, props.rId, index)}
              name={'permission-tracker'}
              sx={{
                color: '#e5e7eb',
                '&.Mui-checked': {
                  color: '#ffd44b',
                },
              }}
              size={'small'}
            />
          </TableCell>
        );
      })}
    </>
  );
};
export default PermissionRadioRow;
