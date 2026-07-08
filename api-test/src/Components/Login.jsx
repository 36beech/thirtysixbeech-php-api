import { useState } from "react";

import { Button } from "./Button";
import { Input } from "./Input";
import { useApi } from "./useApi";

export const Login = ({ onLogin }) => {
  const [pwinput, setPwinput] = useState("");
  const { post, loading, error } = useApi();

  const handleSubmit = () => {
    post("/auth/login", { pin: pwinput }).then((result) => {
      onLogin(result);
    }).catch((error) => console.log(error));
  };
  if( loading ) return <p className="text-center">Logging in</p>
  return (
    <>
      <Input type="password" value={pwinput} onChange={(e) => setPwinput(e.target.value)} maxLength={6} />
      <Button type="button" onClick={handleSubmit}>
        Log in
      </Button>
    </>
  );
};
