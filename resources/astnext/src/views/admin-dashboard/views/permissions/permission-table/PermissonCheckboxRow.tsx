import { FC } from 'react';

import HtmlTooltip from '../../../../../components/base/tooltip/HtmlTooltip';
import TrackerCheckbox from '../../training-tracker/components/TrackerCheckbox';
import { TableCell } from '../Permissions';

const PermissionCheckboxRow: FC<{
  title?: string;
  items: boolean[];
  arrType: number;
  rId: number;
  item_arr: number[] | string[];
  onClick?: (rId: number, cId: number, arrType: number) => void;
  childComp?: React.ReactNode;
}> = ({ title, items, arrType, rId, item_arr, onClick, childComp }) => {
  const classVariable = items.length === 1 ? 'justify-center' : 'justify-around';
  return (
    <TableCell key={`${title}_yhc`} align="center">
      <div className={`flex ${classVariable} flex-row`}>
        {item_arr.map((ele, ind) => {
          return (
            <div key={ind} className="mx-4">
              <HtmlTooltip title={`${ele}`}>
                <span>
                  <TrackerCheckbox
                    checked={items[ind]}
                    onChange={() => onClick(rId, ind, arrType)}
                  />
                </span>
              </HtmlTooltip>
            </div>
          );
        })}
      </div>
      {childComp}
    </TableCell>
  );
};
export default PermissionCheckboxRow;
