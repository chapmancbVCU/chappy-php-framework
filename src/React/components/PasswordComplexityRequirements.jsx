import React from "react";
export const PasswordComplexityRequirements = () =>{
    return (
        <div>
            <h4 className="text-center">Password Requirements</h4>
            <ul className="pl-3">
                {import.meta.env.VITE_PW_MIN_LENGTH && 
                    <li>Minimum {import.meta.env.VITE_PW_MIN_LENGTH} characters in length</li>
                }
                {import.meta.env.VITE_PW_MAX_LENGTH && 
                    <li>Maximum {import.meta.env.VITE_PW_MAX_LENGTH} characters in length</li>
                }
            </ul>
        </div>
    );
}