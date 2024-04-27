import { TableHead, TableRow } from '@mui/material';
import { FC, useState } from 'react';

import Icon from '../../../../../components/base/icon/Icon';
import HtmlTooltip from '../../../../../components/base/tooltip/HtmlTooltip';
import { TableCell } from '../Permissions';
type PermissionHeaderType = {
  title?: string;
  items: string[];
  year_arr?: number[];
  campus_array: string[];
  house_array?: string[];
  onClick?: (id: number) => void;
  onCclick?: (value: boolean, cId: number) => void;
};

const PermissionHeader: FC<PermissionHeaderType> = ({
  items,
  onClick,
  year_arr,
  campus_array,
  house_array,
  onCclick,
}) => {
  return (
    <TableHead className="cohort-data-table-header">
      <TableRow>
        <TableCell
          component="th"
          style={{
            width: '250px',
          }}
        >
          <h3 className="text-[17px] font-bold inline"> All Teachers </h3>
        </TableCell>
        {items.map((item, num) => {
          return (
            <TableCell key={num} component="th" align="center">
              <div className="text-gray-500">
                <p title="Safeguarding" className="truncate mb-2 ">
                  {item}
                </p>
                <div className="flex justify-center flex-row">
                  {num < 5 ? (
                    <p
                      role="button"
                      className="text-[10px] text-gray-400"
                      onClick={onClick.bind(null, num)}
                    >
                      Tick All
                    </p>
                  ) : num === 5 ? (
                    year_arr.map((ele) => (
                      <div key={ele} className="mx-5">
                        {ele}
                      </div>
                    ))
                  ) : num === 6 ? (
                    campus_array.map((ele, i) => (
                      <div key={ele} className="mx-4 grid w-max">
                        <HtmlTooltip title={ele} key={i}>
                          <span className="whitespace-nowrap">{ele.substring(0, 3)}</span>
                        </HtmlTooltip>
                        <AllCampusCheckbox id={i} onClink={onCclick} />
                      </div>
                    ))
                  ) : (
                    house_array.map((ele, i) => (
                      <HtmlTooltip title={ele} key={i}>
                        <div className="mx-4 whitespace-nowrap">{ele.substring(0, 3)}</div>
                      </HtmlTooltip>
                    ))
                  )}
                </div>
              </div>
            </TableCell>
          );
        })}
      </TableRow>
    </TableHead>
  );
};

const AllCampusCheckbox: FC<{ id: number; onClink: (check: boolean, cId: number) => void }> = ({
  id,
  onClink,
}) => {
  const [check, setCheck] = useState<boolean>(false);
  const handClick = () => {
    setCheck(!check);
    onClink(check, id);
  };
  return (
    <HtmlTooltip title="Select/deselect">
      <span>
        <Icon
          role="button"
          icon={check ? 'amber-tick-circle' : 'circle'}
          size="16"
          className="hover:opacity-80"
          onClick={handClick}
        />
      </span>
    </HtmlTooltip>
  );
};
export default PermissionHeader;
