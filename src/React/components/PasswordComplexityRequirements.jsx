import React from "react";
import { env } from "../utils/helpers";
/**
 * Generates component listing password complexity requirements.
 * @returns {HTMLDivElement} Component containing list of password 
 * complexity requirements.
 */
export const PasswordComplexityRequirements = () => {
    return (
        <div>
            <h4 className="text-center">Password Requirements</h4>
            <ul className="pl-3">
                {env('VITE_SET_PW_MIN_LENGTH') === 'true' && 
                    <li>Minimum {env('VITE_PW_MIN_LENGTH')} characters in length</li>
                }
                {env('VITE_SET_PW_MAX_LENGTH') === 'true' && 
                    <li>Maximum {env('VITE_PW_MAX_LENGTH')} characters in length</li>
                }
                {env('VITE_PW_UPPER_CHAR') === 'true' &&
                    <li>At least 1 upper case character</li>
                }
                {env('VITE_PW_LOWER_CHAR') === 'true' &&
                    <li>At least 1 lower case character</li>
                }
                {env('VITE_PW_NUM_CHAR') === 'true' &&
                    <li>At least 1 number</li>
                }
                {env('VITE_PW_SPECIAL_CHAR') === 'true' &&
                    <li>At least 1 special character</li>
                }
            </ul>
        </div>
    );
}