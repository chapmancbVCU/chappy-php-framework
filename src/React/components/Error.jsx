import React from "react";

/**
 * @property {object} error Object containing information about any errors.
 * @param {inputProps} param0 
 * @returns 
 */
export const Error = ({error}) => {
    return (
        <div className="text-danger text-center mt-3">{error.message}</div>
    );
}        