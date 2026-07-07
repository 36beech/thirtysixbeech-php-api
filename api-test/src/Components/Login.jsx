import { useState } from "react";

import { Button } from "./Button";
import { Input } from "./Input";
import { useApi } from "./useApi";

export const Login = ({ onLogin }) => {
  const [pwinput, setPwinput] = useState("");
  const { post, loading, error } = useApi();

  const handleSubmit = () => {
    post("/auth/login", { pin: pwinput }).then((result) => {
      console.log(result);
    });
  };
  return (
    <>
      <Input type="password" value={pwinput} onChange={(e) => setPwinput(e.target.value)} />
      <Button type="button" onClick={handleSubmit}>Log in</Button>
    </>
  );
};
