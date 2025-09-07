import React from "react";
export const PasswordComplexityRequirements = () => {
    const setMinLength = import.meta.env.VITE_SET_PW_MIN_LENGTH === 'true'
    return (
        <div>
            <h4 className="text-center">Password Requirements</h4>
            <ul className="pl-3">
                {import.meta.env.VITE_SET_PW_MIN_LENGTH === 'true' && 
                    <li>Minimum {import.meta.env.VITE_PW_MIN_LENGTH} characters in length</li>
                }
                {import.meta.env.VITE_SET_PW_MAX_LENGTH === 'true' && 
                    <li>Maximum {import.meta.env.VITE_PW_MAX_LENGTH} characters in length</li>
                }
                {import.meta.env.VITE_PW_UPPER_CHAR === 'true' &&
                    <li>At least 1 upper case character</li>
                }
                {import.meta.env.VITE_PW_LOWER_CHAR === 'true' &&
                    <li>At least 1 lower case character</li>
                }
                {import.meta.env.VITE_PW_NUM_CHAR === 'true' &&
                    <li>At least 1 number</li>
                }
                {import.meta.env.VITE_PW_SPECIAL_CHAR === 'true' &&
                    <li>At least 1 special character</li>
                }
            </ul>
        </div>
    );
}